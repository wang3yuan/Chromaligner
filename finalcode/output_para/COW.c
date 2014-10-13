#include <stdio.h>
#include <stdlib.h>
#include <math.h>
#include <string.h>
#include "arg.h"

#define MAX_Default 1000
#define MAX_Range 3600 //30mins
//#define MAX_Range 2520   //21mins
//#define MAX_Range 1920   //16mins
#define MAX_Neg -100000
#define Spike_number 4
//#define Total_minutes 30//mins
//#define Total_timeframe 3600//mins
#define Converge_threshold 0.0001
#define Converge_times 10
#define EOF_end	700
#define EOF_start 50
#define EOF_wid	50
FILE* target;
FILE* sample;
FILE* fout;
FILE* ftmp;

double* target_array;
double* warp_array;
double* sample_array;
double* answer_array;
double* temp_array;
int* target_spike;
int* sample_spike;
double** DP;
int** Warping_para;
double Section_N;
int Slack;
int Section_LP;
int Section_LT;
int max_range;
int spike_number;
int sample_Pcount;/*number of peaks*/
int target_Pcount;
int p_option;

int Malloc(void);
void Free(void);
int Filename(char* Name,char* path1,char* tail);
int new_line(int num,FILE* fp);
int get_data(double* array,FILE* fp,int Pcount,int max_length);/*max_length is the alignment length*/
//int get_data(double* intensity_array,int* spiketime_array,FILE* fp,int Pcount);
int get_spike(int* array,FILE* fp,int spike_num,double freq,int EOF_flag,int End_flag);/*freq= freq_per_sec*/
void Spiking(double* spike_array,int* spike_time,int* pattern_time,int spike_num,int spike_len);
void COW(double* t_array,double* s_array,double* w_array,int s_len,int t_len,int section_LP,int section_LT);
double Benefit_f(double* t_array,double* s_array,double* w_array,int lp,int up,int m,int t_len,int flag);
void interpolate(double* a,int a_len,double* b,int b_len);
//int Auto_Align_eof(double* data_array );
void Align_eof(double* data_array,int eof,int data_len,int max_len);

int main(int argc,char* argv[]){
	int i,j,k,check;/*check is a receiver of return value*/
	double no_datapoints,no_secs,freq_per_sec,eof_flag,end_flag;/*check is a receiver of return value*/
	int Lp;/*length of sample*/
	int Lt;/*length of product*/
	double cor=MAX_Neg,cor_max,temp;
	int answer_s,answer_N;
	char name[MAX_Default];
	//max_range=atoi(argv[5]);
	spike_number=atoi(argv[6]);
	no_datapoints=atof(argv[7]);
	no_secs=atof(argv[8]);
	eof_flag=atof(argv[9]);
	end_flag=atof(argv[10]);
	freq_per_sec=((double)no_datapoints/no_secs);
	max_range=(int)(atof(argv[5])*60*freq_per_sec);

	p_option=0;
	if(eof_flag==-1){
		p_option++;
	}else if(eof_flag<0){
		fprintf(stderr,"A given fixed analysis start time should be bigger than zero.\n");
		return 0;
	}

	if(end_flag==-1){
		p_option++;
	}else if(end_flag<0){
		fprintf(stderr,"A given fixed analysis end time should be bigger than zero.\n");
		return 0;
	}

	/*check if target=sample*/
	if(strcmp(argv[1],argv[2])==0){
		return 0;
	}
	check=Malloc();
	if(check<0){
		return 0;
	}
	/*get spike time*/
	target=fopen(argv[3],"r");
	sample=fopen(argv[4],"r");
	check=get_spike(target_spike,target,spike_number,(double)no_datapoints/(double)no_secs,eof_flag,end_flag);
	if(check){
		fprintf(stderr,"Please check the target parameter file %s.\n",argv[3]);
		return 0;
	}
	check=get_spike(sample_spike,sample,spike_number,(double)no_datapoints/(double)no_secs,eof_flag,end_flag);
	if(check){
		fprintf(stderr,"Please check the sample parameter file %s \n",argv[4]);
		return 0;
	}
	if(eof_flag!=-1){
		target_spike[0]=eof_flag*60*freq_per_sec;
		sample_spike[0]=eof_flag*60*freq_per_sec;
	}
	if(end_flag!=-1){
		sample_spike[spike_number+1]=end_flag*60*freq_per_sec;
		target_spike[spike_number+1]=end_flag*60*freq_per_sec;
	}
	fclose(sample);
	fclose(target);
	/*get retention time data*/
	target=fopen(argv[1],"r");
	sample=fopen(argv[2],"r");

	strcpy(name,"./aligned_retentiontime_file/");
	check=Filename(name,argv[2],"-processed.txt");
	if(check){
		return 0;
	}
	fout=fopen(name,"w");

		//printf("sample_spikes[1]:%d spike_num:%d\n",sample_spike[1],spike_number+1);
	check=get_data(sample_array,sample,sample_spike[spike_number+1],max_range);
	if(check){
		fprintf(stderr,"Please check sample file %s and target file %s.\n",argv[2],argv[1]);
		return 0;
	}
	Lp=max_range;/*?*/
	check=get_data(target_array,target,target_spike[spike_number+1],max_range);
	if(check){
		fprintf(stderr,"Please check sample file %s and target file %s.\n",argv[2],argv[1]);
		return 0;
	}
	Lt=max_range;/*?*/
	fclose(sample);
	fclose(target);
	/*
	   printf("*********origine*********\n");
	   for(i=0;i<max_range;i++){
	   printf("%.3lf\n",sample_array[i]);
	   }
	   printf("*********spike*********\n");
	   for(i=0;i<max_range;i++){
	   printf("%.3lf\n",sample_array[i]);
	   }*/

	/*target_spike[0] and sample_spike[0] are eof_time
for(i=0;i<spike_number+2;i++){
	  printf("t:%d\n",target_spike[i]);
	  }
	  for(i=0;i<spike_number+2;i++){
	  printf("s:%d\n",sample_spike[i]);
	  }*/
	Align_eof(target_array,target_spike[0],target_spike[spike_number+1],max_range);
	for(i=1;i<=spike_number;i++){
		target_spike[i]-=target_spike[0];	
	}
	target_spike[0]=0;
	target_spike[spike_number+1]=max_range;
	Align_eof(sample_array,sample_spike[0],sample_spike[spike_number+1],max_range);
	for(i=1;i<=spike_number;i++){
		sample_spike[i]-=sample_spike[0];	
	}
	sample_spike[0]=0;
	sample_spike[spike_number+1]=max_range;
	strcpy(name,"./aligned_retentiontime_file/");
	check=Filename(name,argv[1],"-processed.txt");
	if(check){
		return 0;
	}

	ftmp=fopen(name,"w");
	if(ftmp==NULL){
	    fprintf(stderr,"Can't create file %s. fp returns NULL\n",name);
	    return 0;
	}
	//for(i=0;i<target_spike[spike_number+1];i++){
	for(i=0;i<max_range;i++){
		fprintf(ftmp,"%lf\n",target_array[i]);
	}
	fclose(ftmp);
	name[0]=0;
	check=Filename(name,argv[2],".txt");
	if(check){
		return 0;
	}
	ftmp=fopen(name,"w");
	if(ftmp==NULL){
	    fprintf(stderr,"Can't create file %s. fp returns NULL\n",name);
	    return 0;
	}

	//for(i=0;i<sample_spike[spike_number+1];i++){
	for(i=0;i<max_range;i++){
		fprintf(ftmp,"%lf\n",sample_array[i]);
	}
	fclose(ftmp);


	Spiking(sample_array,sample_spike,target_spike,spike_number,Lp);

	/*int convtimes;
	  double diff;
	  diff=0;
	  convtimes=0;
	  while(cor-diff>Converge_threshold&&convtimes<=Converge_times){
	  printf("*********time:%d diff:%.3lf ***********\n",convtimes+1,cor-diff);
	  convtimes++;
	  diff=cor;*/

	ftmp=fopen("log.txt","w");
//	fprintf(stdout,"Now we are aligning %s with %s. Please wait a while.\n",argv[2],argv[1]);
	for(i=0;i<=spike_number;i++){
double Benefit_f(double* t_array,double* s_array,double* w_array,int lp,int up,int m,int t_len,int flag);
		cor=Benefit_f(&target_array[target_spike[i]],&sample_array[target_spike[i]],&sample_array[target_spike[i]],0,target_spike[i+1]-target_spike[i],0,target_spike[spike_number+1],1);
		cor_max=cor;
		fprintf(ftmp,"%d cor:%lf\n",i,cor);
//		printf("len:%d\n",target_spike[spike_number+1]);
		//Lp=sample_spike[i+1]-sample_spike[i];
		Lt=target_spike[i+1]-target_spike[i];
		Lp=Lt;
		for(Section_N=2;Section_N<=Lt/20;Section_N++){
			for(Slack=1;Slack<=5;Slack++){
				//printf("new%d\n",i);
				DP=malloc(sizeof(double*)*(Section_N+1));
				Warping_para=malloc(sizeof(int*)*(Section_N+1));
				for(j=0;j<=Section_N;j++){
					DP[j]=malloc(sizeof(double)*(Lt+1));
					for(k=0;k<=Lt;k++){
						DP[j][k]=MAX_Neg;
					}
				}
				for(j=0;j<Section_N;j++){
					Warping_para[j]=malloc(sizeof(int)*(Lt+1));
					for(k=0;k<Lt;k++){
						Warping_para[j][k]=MAX_Neg;
					}
				}
				Section_LP=(double)Lp/Section_N;
				Section_LT=(double)Lt/Section_N;
				//				printf("Lp:%d Lt:%d Section_LP:%d Section_LT:%d\n",Lp,Lt,Section_LP,Section_LT);
				COW(&target_array[target_spike[i]] ,&sample_array[target_spike[i]] ,&warp_array[target_spike[i]] ,Lp ,Lt ,Section_LP ,Section_LT);
				free(DP);
				free(Warping_para);
				temp=Benefit_f(&target_array[target_spike[i]],&sample_array[target_spike[i]],&warp_array[target_spike[i]],0,target_spike[i+1]-target_spike[i],0,target_spike[spike_number+1],1);
				//		printf("cor:%lf\n",temp);
					fprintf(ftmp,"here s:%d N:%.0lf cor:%lf\n",Slack,Section_N,cor_max);
				if(temp>cor_max){
					cor_max=temp;
//					fprintf(ftmp,"here s:%d N:%.0lf cor:%lf\n",Slack,Section_N,cor_max);
					for(j=target_spike[i];j<max_range;j++){
						answer_array[j]=warp_array[j];
						answer_s=Slack;
						answer_N=Section_N;
					}
				}	
			}
		}
		if(cor==cor_max){
			for(j=target_spike[i];j<max_range;j++){
				answer_array[j]=sample_array[j];
				answer_s=Slack;
				answer_N=Section_N;
			}

		}
		//		fprintf(ftmp,"%d\n%d\n",answer_N,answer_s);
		/*
		   printf("****************\n");
		   for(j=0;j<max_range;j++){
		   printf("%.3lf\n",warp_array[j]);
		   }*/
	}
/*	for(i=0;i<max_range;i++){
	sample_array[i]=answer_array[i];
	}
	}*/

	fclose(ftmp);
	for(i=0;i<max_range;i++){
		fprintf(fout,"%lf\n",answer_array[i]);
	}

	fclose(fout);
	Free();
	fprintf(stdout,"%s is aligned.\n",argv[2]);
	return 0;

}


int Malloc( ){
	int i,j;
	target_array=malloc(max_range*sizeof(double));
	if(target_array==NULL){
		fprintf(stderr,"Memory is not sufficient.\n");
		return -1;	
	}
	warp_array=malloc(max_range*sizeof(double));
	if(warp_array==NULL){
		fprintf(stderr,"Memory is not sufficient.\n");
		return -1;	
	}
	sample_array=malloc(max_range*sizeof(double));
	if(sample_array==NULL){
		fprintf(stderr,"Memory is not sufficient.\n");
		return -1;	
	}
	answer_array=malloc(max_range*sizeof(double));
	if(answer_array==NULL){
		fprintf(stderr,"Memory is not sufficient.\n");
		return -1;	
	}
	target_spike=malloc((spike_number+2)*sizeof(int));
	if(target_spike==NULL){
		fprintf(stderr,"Memory is not sufficient.\n");
		return -1;	
	}
	sample_spike=malloc((spike_number+2)*sizeof(int));
	if(sample_spike==NULL){
		fprintf(stderr,"Memory is not sufficient.\n");
		return -1;	
	}
	temp_array=malloc(max_range*sizeof(double));
	if(temp_array==NULL){
		fprintf(stderr,"Memory is not sufficient.\n");
		return -1;	
	}

	return 0;
}

void Free(){
	free(target_array);
	free(warp_array);
	free(sample_array);
	free(temp_array);
	free(answer_array);
	free(target_spike);
	free(sample_spike);
	return;
}
int Filename(char* Name,char* path1,char* tail){
	char name_tmp[MAX_Default];
	int i,j,name_len;
	strcat(Name,path1);
	for(i=strlen(Name)-1;i>=0;i--){
		if(Name[i]=='.'){
			name_tmp[0]=Name[i+1];
			name_tmp[1]=Name[i+2];
			name_tmp[2]=Name[i+3];
			name_tmp[3]=0;
			if(strcmp(name_tmp,"txt")!=0){
				fprintf(stdout,"Please check the \"input.txt\". The first colum should be \"real\" file name.");
				fprintf(stdout,"And we only accept the txt file format.\n");
				return -1;
			}
			Name[i]=0;
			break;
		}
	}
	if(i==0){
		fprintf(stdout,"Please check the \"input.txt\". The first colum should be \"real\" file name.\n");
		return -1;
	}
	strcat(Name,tail);
	return 0;
}

int new_line(int num,FILE* fp){
	char tmp;
	int i=0;
	while(fscanf(fp,"%c",&tmp)==1){
		if(tmp=='\n'){
			i++;
			if(i==num){
				return 0;
			}
		}
	}
	fprintf(stderr,"new_line can't detect correct number of indicated newline.\n");
	return -1;
}

int get_data(double* intensity_array,FILE* fp,int Pcount,int max_length){
	int i,eof_time,array_index;
	double height,ret_time;
	array_index=0;
	for(i=0;i<max_range;i++){
		intensity_array[i]=0;
	}
	i=0;
	while(fscanf(fp,"%lf",&height)!=EOF&&i<Pcount){
		intensity_array[i]=height;
		new_line(1,fp);
		i++;
	}
	if(i<Pcount){
		fprintf(stderr,"The number of rows (%d) in file is less than the endtime (%d) for peak alignment.",i,Pcount);
		return -1;
	}
	if(Pcount<max_length){
//		printf("Pcount:%d fill\n",Pcount);
		for(i=Pcount;i<max_length;i++){
			intensity_array[i]=intensity_array[i-1];
		}
	}
	return 0;
}

/*array[0]:eof_time  array[spike_num+1]:end*/
int get_spike(int* array,FILE* fp,int spike_num,double freq,int EOF_flag,int End_flag){
	int i,num;
	double ret_time;
	char string_temp[MAX_Default];
	if(EOF_flag==-1){
		num=0;
	}else{
		num=1;
	}
		//num=0;
	freq*=60;
	while(fscanf(fp,"%lf",&ret_time)!=EOF){
		array[num]=(int)(freq*ret_time);
		//printf("time%d:%d\n",num,array[0]);
		num++;
	}

	/*num means the number of lines in the read file*/
	if(EOF_flag!=-1){
		num--;
	}
	if(num!=spike_num+p_option){
		fprintf(stderr,"Your spike peaks number is %d. ",spike_num);
		if(EOF_flag==-1){
			fprintf(stderr,"You don't give a fixed analysis startime(Ex: EOF). ");
		}
		if(End_flag==-1){
			fprintf(stderr,"You don't give a fixed analysis endtime. ");
		}
		fprintf(stderr,"So in parameter files, each one should contain %d rows exactly\n",spike_num+p_option);
		return -1;
	}else{
		return 0;
	}
}



void Spiking(double* spike_array,int* spike_time,int* pattern_time,int spike_num,int spike_len){
	int i;
	for(i=0;i<pattern_time[spike_number+1];i++){
		temp_array[i]=0;
	}
	for(i=1;i<spike_num+2;i++){
		interpolate(&temp_array[pattern_time[i-1]],pattern_time[i]-pattern_time[i-1],
				&spike_array[spike_time[i-1]],spike_time[i]-spike_time[i-1]);
	}
	for(i=0;i<spike_len;i++){
		spike_array[i]=temp_array[i];
	}
	return ;
}

void Align_eof(double* data_array,int wave_eof,int data_len,int max_len){
	int i;
	for(i=wave_eof;i<data_len;i++){
		data_array[i-wave_eof]=data_array[i];
	}
	//for(i=data_len-wave_eof;i<data_len;i++){
	for(i=data_len-wave_eof;i<max_len;i++){
		data_array[i]=data_array[i-1];
	}

	return ;
}

/*int Auto_Align_eof(double* data_array){
  int i,wave_time,wave_halfwid,wave_eof;
  double slope,wave_min,wave_halfhei,baseline;
  wave_min=data_array[EOF_start];
  wave_time=EOF_start;
  slope=data_array[EOF_start]-data_array[EOF_start-1];
  for(i=EOF_start+1;i<EOF_end;i++){
  if(slope<0 && (data_array[i]-data_array[i-1])>0){
  if(data_array[i-1]<wave_min){
  wave_min=data_array[i-1];
  wave_time=i-1;		
  printf("%d %lf\n",i-1,data_array[i-1]);
  }
  }
  slope=data_array[i]-data_array[i-1];
  }
  baseline=data_array[wave_time-EOF_wid]-1;
  for(i=wave_time-EOF_wid;i<wave_time;i++){
  if(fabs(baseline-data_array[i])>0.5*fabs(baseline-wave_min)&&(baseline!=data_array[i])){
  wave_halfhei=data_array[i];
  wave_halfwid=i;
//	printf("wave_min:%lf base:%lf half wid:%d hei:%lf\n",wave_min,baseline,wave_halfwid,wave_halfhei);
wave_eof=(int)((baseline-wave_min)/(wave_halfhei-wave_min));//wave height ratio
wave_eof=(wave_time-wave_halfwid)*wave_eof;
//	printf("QQ wave_eof:%d wave_time:%d\n",wave_eof,wave_time);
wave_eof=wave_time+wave_eof;
break;
}
}
for(i=wave_eof;i<max_range;i++){
data_array[i-wave_eof]=data_array[i];
}
for(i=max_range-wave_eof;i<max_range;i++){
data_array[i]=data_array[i-1];
}
return wave_eof;
}*/
void interpolate(double* A,int A_len,double* B,int B_len){
	int i;
	double point,interpo_para;
	interpo_para=(double)A_len/(double)B_len;
	if(A_len!=B_len){
		for(i=0;i<A_len-1;i++){
			point=(double)i/interpo_para;
			if(point==(int)point){
				A[i]=B[(int)point];
			}else{
				A[i]=(point-(int)point)*(B[ceiling(point)]-B[(int)(point)]);
				A[i]+=B[(int)point];
			}
		}
		A[A_len-1]=B[B_len-1];

	}else{
		for(i=0;i<A_len;i++){
			A[i]=B[i];
		}
	}
	return ;
}

int ceiling(double Double_c){
	double check;
	check=Double_c-(int)Double_c;
	if(check==0){
		return (int)Double_c;
	}
	else{
		return (int)Double_c+1;
	}
}

double Benefit_f(double* t_array,double* s_array,double* w_array,int lp , int up,int m,int t_len,int flag){
	if(lp+1==up){
		/*if(t_array[lp]==s_array[lp]){
		  return 1;
		  }else{*/
		return 0;
		//}
	}
	int i;
	double target_mean,sample_mean,target_std,sample_std,Cov,score;
	if(lp+m>t_len){
		m=t_len-lp;
	}
	if(!flag){
		interpolate(&w_array[lp],m,&s_array[lp],up-lp);
	}
	target_mean=0;
	sample_mean=0;
	for(i=lp;i<up;i++){
		target_mean+=t_array[i];
		sample_mean+=w_array[i];
	}
	target_mean/=(double)(up-lp);
	sample_mean/=(double)(up-lp);
//printf("t_mean:%lf w_mean:%lf\n",target_mean,sample_mean);
	Cov=0;
	target_std=0;
	sample_std=0;
	for(i=lp;i<up;i++){
		Cov+=(t_array[i]-target_mean)*(w_array[i]-sample_mean);
		target_std+=pow(t_array[i]-target_mean,2);
		sample_std+=pow(w_array[i]-sample_mean,2);
	}
//printf("t_std:%lf w_std:%lf\n",target_std,sample_std);
	target_std/=(double)(up-lp);
	sample_std/=(double)(up-lp);
	Cov/=(double)(up-lp);
	target_std=pow(target_std,0.5);
	sample_std=pow(sample_std,0.5);
	score=target_std*sample_std;
	if(score==0){

		score=1;
		Cov=0;
	}
	//	printf("Cov:%lf target_std:%lf sample_std:%lf  ",Cov,target_std,sample_std);
	score=Cov/score;
	return score;
}


void COW(double* t_array,double* s_array,double* w_array,int s_len,int t_len,int section_LP,int section_LT){
	int i,j,k,u,x,xstart,xend,m,m_end,tmp,delta,delta_end,section_LT_end;
	int* result;
	double fsum;
	result=malloc(sizeof(int)*(Section_N+1));

	/*?*/
	DP[(int)Section_N][t_len]=0;
	m=section_LP;
	m_end=s_len-section_LP*(Section_N-1);
	//printf("s_len:%d m:%d m':%d\n",s_len,m,m_end);
	delta=section_LT-m;
	delta_end=t_len-section_LT*(Section_N-1)-m_end;
	//printf("d:%d d':%d\n",delta,delta_end);

	xstart=(Section_N-1)*(section_LT-Slack);
	section_LT_end=(Section_N-1)*section_LT;
	tmp=section_LT_end-Slack;
	//printf("xstart:%d tmp:%d\n",xstart,tmp);
	if(tmp>xstart){
		xstart=tmp;
	}

	//printf("xstart:%d \n",xstart);

	xend=(Section_N-1)*(section_LT+Slack);
	tmp=section_LT_end+Slack;
	//printf("xend:%d tmp:%d\n",xend,tmp);
	if(tmp<xend){
		xend=tmp;
	}
	//printf("xend:%d \n",xend);
	for(x=xstart;x<=xend;x++){
		for(u=delta_end-Slack;u<=delta_end+Slack;u++){
			if(x+u+m_end<=t_len && x>=0 && m+u>=0){
				//printf("\nx:%d m:%d u:%d x+u+m:%d\n",x,m_end,u,x+u+m_end);
				fsum=DP[(int)Section_N][x+m_end+u]+Benefit_f(t_array,s_array,w_array,x,x+m_end+u,m_end,t_len,0);
				//printf("fsum:%lf DP[][%d]:%lf Ben:%lf\n",fsum,x+m_end+u,DP[(int)Section_N][x+m_end+u],Benefit_f(x,x+m_end+u,m_end));
				if(fsum>DP[(int)Section_N-1][x]){
					DP[(int)Section_N-1][x]=fsum;
					Warping_para[(int)Section_N-1][x]=u;
				}
			}
		}
	}
	//	delta=Lt-m;
	for(i=Section_N-2;i>=0;i--){
		xstart=i*(m+delta-Slack);
		tmp=-(Section_N-i-1)*(m+delta+Slack)+section_LT_end-Slack;
		if(tmp>xstart){
			xstart=tmp;
		}
		xend=i*(m+delta+Slack);
		tmp=-(Section_N-i-1)*(m+delta-Slack)+section_LT_end+Slack;
		if(tmp<xend){
			xend=tmp;
		}

		for(x=xstart;x<=xend;x++){
			for(u=delta-Slack;u<=delta+Slack;u++){
				if(x+m+u<=t_len && x>=0 && m+u>=0){
					fsum=DP[i+1][x+m+u]+Benefit_f(t_array,s_array,w_array,x,x+m+u,m,t_len,0);
					if(fsum>DP[i][x]){
						DP[i][x]=fsum;
						Warping_para[i][x]=u;
					}
				}
			}
		}
	}
	result[0]=0;
	for(i=0;i<Section_N-1;i++){
		result[i+1]=result[i]+m+Warping_para[i][result[i]];
	}
	result[(int)Section_N]=result[(int)Section_N-1]+m_end+Warping_para[(int)Section_N-1][result[(int)Section_N-1]];
	for(i=0;i<Section_N-1;i++){
		interpolate(&w_array[result[i]],result[i+1]-result[i],&s_array[i*m],m);
	}
	interpolate(&w_array[result[(int)Section_N-1]],result[(int)Section_N]-result[(int)Section_N-1],&s_array[(int)(Section_N-1)*m],m_end);
	w_array[0]=s_array[0];
	w_array[(int)t_len-1]=s_array[(int)s_len-1];
	free(result);
	return ;
}
