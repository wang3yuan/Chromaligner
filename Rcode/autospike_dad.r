input=as.matrix(read.table("index.txt",header=T,sep="\t",fill=T))

file_name=input[,1]
par_name=paste("P",file_name,sep="")
nsample=length(file_name)

spoint=matrix(rep(0,nsample),byrow=F)
epoint=matrix(rep(0,nsample),byrow=F)
dos=60*(no_datapoint/no_secs)
if(s_flag==1)
  spoint=matrix(rep(floor(start_time*dos),nsample),byrow=F)
if(start_time==0 & s_flag==1)
  spoint=spoint+1
if(e_flag==1)
  epoint=matrix(rep(floor(end_time*dos),nsample),byrow=F)
for(i in 1:nsample)
{
  if(s_flag==-1) {  spoint[i]=round((min(read.table(par_name[i],header=F,sep="\t",fill=T)))*dos)
    if(spoint[i]==0) spoint[i]=1}
  if(e_flag==-1) {  epoint[i]=round((max(read.table(par_name[i],header=F,sep="\t",fill=T)))*dos)
    if(epoint[i]==0) epoint[i]=1}
}
sample_name=file_name
ssegment=list()
esegment=list()

shift=1; # shift min
maxmin=5
window_size=10
min_cor=0.98


for(n in 1:nsample)
{
  ssegment[[n]]=rep(0,nsegment)
  esegment[[n]]=rep(0,nsegment)
  for(seg.index in 1:nsegment)
  {
   ssegment[[n]][seg.index]=floor((epoint[n]-spoint[n]+1)/nsegment)*(seg.index-1)+spoint[n]+1
   esegment[[n]][seg.index]=floor((epoint[n]-spoint[n]+1)/nsegment)*(seg.index)+spoint[n]
  }
  esegment[[n]][nsegment]=epoint[n]
}

dad.info=list()
intensity=list()
peak.time=list()
is.peak=list()
r.time=list()
baseline=list()
peak.location=list()

source("../Baseline_correction_function_dad.r")
dir.create("baseline")
for(i in 1:length(file_name)) 
{
 #sample_name[i]=as.character(read.table(file_name[i],header=F,sep=",",skip=(location_sample_name-1),fill=T,nrow=5)[1,2]) 
 wave=read.table(file_name[i],header=F,sep=",",skip=(location_wave_table-1),fill=T,nrow=5)[1,] 
 dad.info[[i]]=read.table(file_name[i],header=F,sep=",",skip=(rows_of_description),fill=T)
 colnames(dad.info[[i]])=c("rt", as.numeric(wave[-1]))
 rownames(dad.info[[i]])=dad.info[[i]][,1]
 intensity[[i]]=matrix(dad.info[[i]][,wave==wave_length]) 
 #write.table(intensity[[i]],file_name[i],row.name=F,col.name=F,sep="\t")
 #r.time[[i]]=dad.info[[i]][,1] 
 rownames(intensity[[i]])=dad.info[[i]][,1]
 
 n=1.5
 win_size=11
 peak_points=floor(25/no_secs); #25 secs per peak
 intensity[[i]]=intensity[[i]]-min(intensity[[i]])
 r.time[[i]]=(1:length(intensity[[i]]))*no_secs/60
 baseline[[i]]=baseline_correction(intensity[[i]],r.time[[i]],peak_points,n, win_size)
 #windows(); bringToTop(stay=T);
 png(filename =paste("baseline",paste(sample_name[i],".png",sep=""),sep="/"), units = "px", width=1024, height=756)  
 plot(r.time[[i]],intensity[[i]],type="l",main=file_name[i])
 lines(r.time[[i]],baseline[[i]],col=2)
 intensity[[i]]=intensity[[i]]-baseline[[i]]
 intensity[[i]][intensity[[i]]<=0]=0
 intensity[[i]]=intensity[[i]]+1  
 #lines(r.time[[i]],intensity[[i]],col=3)
 dev.off() 
  #write.table(intensity[[i]],file=file_name[i],row.names=F,col.names=F,sep="\t")

 if(file_name[i]==target)
 {
  t.intensity=intensity[[i]]
  t.dad=dad.info[[i]]
  t.r.time=r.time[[i]]
  t.index=i
 }
}

#retation time
t.peak.time=t.r.time
for(n in 1:nsample)
{
  peak.time[[n]]=r.time[[n]]
}

old=F;
new=T;
msigma=rep(0,nsample);
if(old){
#sigma                         
nseg=32; 
for(n in 1:nsample)
{
 sigma=rep(0,nseg)
 lspectrum=length(intensity[[n]])
 lsegment=ceiling(length(intensity[[n]])/nseg)
for(i in 1:(nseg-1))
{
  sigma[i]=sd(intensity[[n]][((i-1)*lsegment+1):(i*lsegment)])
 i=i+1
  sigma[i]=sd(intensity[[n]][((i-1)*lsegment+1):lspectrum])
}
 msigma[n]=median(sigma[(sigma>0)& !(is.na(sigma))])
 #msigma[n]=3*(min(sigma[sigma>0]))/(1+length(sigma[sigma==0]))
} 
}
if(new){
 peak_width=floor(25/no_secs/2); #25 secs per peak
 for(n in 1:nsample){
  sigma=rep(0,length(intensity[[n]]))
  for(i in (1+peak_width):(length(intensity[[n]])-peak_width)){
   sigma[i]=sd(intensity[[n]][(i-peak_width):(i+peak_width)])
  }
  msigma[n]=quantile(sigma, probs=c(0.75))
 }
}

#find peaks of target
dir.create("get_peak")
if(old)
for(n in 1:nsample)
{  
 is.peak[[n]]=intensity[[n]]*0
 for(i in spoint[n]:epoint[n])
 {
  if(i <=(spoint[n]+window_size) & intensity[[n]][i]==max(intensity[[n]][spoint[n]:(i+window_size)]))
  {
   if((max(intensity[[n]][spoint[n]:(i+window_size)])-min(intensity[[n]][spoint[n]:(i+window_size)]))>=msigma[n])
    is.peak[[n]][i]=1
  } 
  if(i >=(epoint[n]-window_size) & intensity[[n]][i]==max(intensity[[n]][(epoint[n]-window_size):epoint[n]]))
  {
   if((max(intensity[[n]][(epoint[n]-window_size):epoint[n]])-min(intensity[[n]][(epoint[n]-window_size):epoint[n]]))>=msigma[n])
    is.peak[[n]][i]=1
  }
  if((i>window_size & i<(epoint[n]-window_size)))
  {
   if(intensity[[n]][i]==max(intensity[[n]][(i-window_size):(i+window_size)]))
    if((max(intensity[[n]][(i-window_size):(i+window_size)])-min(intensity[[n]][(i-window_size):(i+window_size)]))>=msigma[n])
     is.peak[[n]][i]=1
  }
 }
} 
if(new)
for(n in 1:nsample)
{  
 is.peak[[n]]=intensity[[n]]*0
 for(i in (spoint[n]+window_size):(epoint[n]-window_size))
 {
  if(intensity[[n]][i]==max(intensity[[n]][(i-window_size):(i+window_size)]))
   if(diff(range(intensity[[n]][(i-window_size):(i+window_size)]))>=msigma[n])
    is.peak[[n]][i]=1
 }
 #windows(); bringToTop(stay=T)
 png(filename =paste("get_peak",paste(sample_name[n],".png",sep=""),sep="/"), units = "px", width=1024, height=756) 
 plot(r.time[[n]], intensity[[n]], main=sample_name[n], type="l")
 points(r.time[[n]][is.peak[[n]]==1], intensity[[n]][is.peak[[n]]==1], col=2)
 dev.off()
} 
t.is.peak=is.peak[[t.index]]

#make peak location table
for(n in 1:nsample)
{
 peak.location[[n]]=rbind(r.time[[n]][is.peak[[n]]==1],which(is.peak[[n]]==1),intensity[[n]][is.peak[[n]]==1])
 rownames(peak.location[[n]])=c("rt","scan","intensity")  
}

match_peak_table=matrix(0,nrow=nsample, ncol=length(peak.location[[t.index]]["rt",])); rownames(match_peak_table)=sample_name
match_peak_table[t.index,]=peak.location[[t.index]]["rt",]

for(n in 1:nsample)
{
 if(n!=t.index)
 {
  for(p in 1:length(peak.location[[t.index]]["rt",]))
  {
   target_peak=peak.location[[t.index]]["rt",p]
   target_peak_intensity=peak.location[[t.index]]["intensity",p]
   target_index=peak.location[[t.index]]["scan",p]
   if(length(peak.location[[n]]["rt",abs(peak.location[[n]]["rt",]-target_peak)<=shift])>0)
   {
    candidate_peak=peak.location[[n]]["rt",abs(peak.location[[n]]["rt",]-target_peak)<=shift]
    candidate_index=peak.location[[n]]["scan",abs(peak.location[[n]]["rt",]-target_peak)<=shift]
    candidate_peak_intensity=peak.location[[n]]["intensity",abs(peak.location[[n]]["rt",]-target_peak)<=shift]

    tar_t=dad.info[[t.index]][target_index,-1];colnames(tar_t)=""
    ref_t=dad.info[[n]][candidate_index,-1];colnames(ref_t)=""
    intensity_table=rbind(tar_t,ref_t)
    cor_list=cor(t(intensity_table))[1,-1]
    if(max(cor_list)>min_cor){
     if(length(which(cor_list>0.99))>1){
      match_peak_table[n,p]=candidate_peak[cor_list>0.99][which.min(abs(candidate_peak[cor_list>0.99]-target_peak))]
     } else{
       match_peak_table[n,p]=candidate_peak[which.max(cor_list)]
      }
    }
   }
  }  
 }
}
 
match_peak_table
for(n in 1:nsample)
for(p in 1:(length(peak.location[[t.index]]["rt",])-1))
{
 delete=-1
 if(match_peak_table[n,p]==match_peak_table[n,p+1])
 {
  delete=match_peak_table[n,p]
  match_peak_table[n,match_peak_table[n,]==delete]=-1*abs(match_peak_table[n,match_peak_table[n,]==delete])
 }
}
                                    

constraint_peak_index=rep(0,length(peak.location[[t.index]]["rt",]))
for(p in 1:length(peak.location[[t.index]]["rt",]))
{
 if(min(match_peak_table[,p])>0)
 constraint_peak_index[p]=p
}                                         
constraint_peak_index=constraint_peak_index[constraint_peak_index!=0]
#constraint_peak_index=rbind(constraint_peak_index,(c(1:length(constraint_peak_index))/length(constraint_peak_index)))

output_peak_index=rep(0,nsegment)
number_constraint_peak_in_segment=rep(0,nsegment)
for(i in 1:nsegment)
{

  segment_head=start_time+(end_time-start_time)/nsegment*(i-1)
  segment_tail=start_time+(end_time-start_time)/nsegment*(i)
  number_constraint_peak_in_segment[i]=length( match_peak_table[t.index,constraint_peak_index][
          match_peak_table[t.index,constraint_peak_index]<=segment_tail &
          match_peak_table[t.index,constraint_peak_index]>segment_head] )
 #match_peak_table[t.index,constraint_peak_index]
}
if(min(number_constraint_peak_in_segment)>=1)
{  
 base=0
 for(i in 1:nsegment)
 {
  output_peak_index[i]=constraint_peak_index[base+round(number_constraint_peak_in_segment[i]/2)]
  base=base+number_constraint_peak_in_segment[i]
 }
}

if(min(number_constraint_peak_in_segment)==0)
{
 if(nsegment<=3)
 {
  constraint_peak_index=rbind(constraint_peak_index, ((1:length(constraint_peak_index))/length(constraint_peak_index)))
  for(i in 1:nsegment)
  output_peak_index[i]=constraint_peak_index[1,which.min(abs(constraint_peak_index[2,]-(i/(1+nsegment))))]
 }
 if(nsegment>3)
 {
  output_peak_index[1]=min(constraint_peak_index)
  output_peak_index[nsegment]=max(constraint_peak_index)
  constraint_peak_index=rbind(constraint_peak_index[1:(length(constraint_peak_index))], ((1:(length(constraint_peak_index)))/(length(constraint_peak_index))))
  for(i in 2:(nsegment-1))
  output_peak_index[i]=constraint_peak_index[1,2:(length(constraint_peak_index[1,])-1)][which.min(abs(constraint_peak_index[2,2:(length(constraint_peak_index[1,])-1)]-(i/(1+nsegment))))]
 } 
}
T.result=t(match_peak_table[,output_peak_index])


#write.table(R.result,"Relation_info.csv",sep=",")
write.table(T.result,"Para_info.csv",sep=",")
write.table(match_peak_table,"Time_info.csv",sep=",")
match_peak_table


for(n in 1:nsample)
{
 para=T.result[,n]
 if(s_flag==-1)
  para=c((spoint[n]/dos),para)
 if(s_flag==-1)
  para=c(para,(epoint[n]-1)/dos) 
 write.table(cbind(intensity[[n]],r.time[[n]]),file_name[n],row.names=F,col.names=F,sep="\t")
 write.table(para,paste("P",file_name[n],sep=""),row.names=F,col.names=F,sep="\t")
}



for(n in 1:nsample)
{
png(filename =paste(sample_name[n],"png",sep="_spike."), units = "px", width=1024, height=756)
plot(r.time[[n]],intensity[[n]],type="l",main=sample_name[n])
plot.new
points(r.time[[n]]*is.peak[[n]],intensity[[n]]*is.peak[[n]],pch=16,col=8)
points(T.result[,n],T.result[,n]*0,pch=16,col=c(1:7))
dev.off()
}

