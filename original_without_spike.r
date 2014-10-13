input=as.matrix(read.table("index.txt",header=T,sep="\t",fill=T))
file_name=input[,1]
par_name=paste("P",file_name,sep="")
nsample=length(file_name)

spoint=matrix(rep(0,nsample),byrow=F)
epoint=matrix(rep(0,nsample),byrow=F)        

if(s_flag==1)
  spoint=matrix(rep((start_time),nsample),byrow=F)
if(e_flag==1)
  epoint=matrix(rep((end_time),nsample),byrow=F)
for(i in 1:nsample)
{
  if(s_flag==-1) {  spoint[i]=((min(read.table(par_name[i],header=F,sep="\t",fill=T))))}

  if(e_flag==-1) {  epoint[i]=((max(read.table(par_name[i],header=F,sep="\t",fill=T))))}

}                  

for(n in 1:nsample)
{
if((s_flag==-1) & (e_flag==-1))
 write.table(rbind((spoint[n]),(epoint[n])),paste("P",file_name[n],sep=""),row.names=F,col.names=F,sep="\t")
if((s_flag==-1) & (e_flag==1))
 write.table((spoint[n]),paste("P",file_name[n],sep=""),row.names=F,col.names=F,sep="\t")
if((s_flag==1) & (e_flag==-1))
 write.table((epoint[n]),paste("P",file_name[n],sep=""),row.names=F,col.names=F,sep="\t")
if((s_flag==1) & (e_flag==1))
 write.table(c(),paste("P",file_name[n],sep=""),row.names=F,col.names=F,sep="\t")
}
