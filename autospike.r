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

spike=matrix(rep(0,nsegment),byrow=F)

intensity=list()

for(i in 1:nsample)
{
intensity[[i]]=read.table(file_name[i],header=F,sep="\t",fill=T)[,1]
  if(s_flag==-1) {  spoint[i]=round((min(read.table(par_name[i],header=F,sep="\t",fill=T)))*dos)
    if(spoint[i]==0) spoint[i]=1}
  if(e_flag==-1) {  epoint[i]=round((max(read.table(par_name[i],header=F,sep="\t",fill=T)))*dos)
    if(epoint[i]==0) epoint[i]=1}
}
ssegment=list()
esegment=list()
for(n in 1:nsample)
{
  ssegment[[n]]=rep(0,nsegment)
  esegment[[n]]=rep(0,nsegment)
  for(seg.index in 1:nsegment)
  {
   ssegment[[n]][seg.index]=floor((epoint[n]-spoint[n]+1)/nsegment)*(seg.index-1)+1+spoint[i]
   esegment[[n]][seg.index]=floor((epoint[n]-spoint[n]+1)/nsegment)*(seg.index)+spoint[i]
  }
  esegment[[n]][nsegment]=epoint[n]
}


for(i in 1:nsample)
{
 png(filename =paste(file_name[i],".png",sep=""))
 plot(seq(spoint[i],epoint[i],1)/dos,intensity[[i]][spoint[i]:epoint[i]],type="l")
 plot.new
 title(file_name[i])
 for(j in 1:nsegment)
 {
  spike[j]=order(intensity[[i]][ssegment[[n]][j]:esegment[[n]][j]])[length(intensity[[i]][ssegment[[n]][j]:esegment[[n]][j]])]+ssegment[[n]][j]-1
  points(spike[j]/dos,intensity[[i]][spike[j]],pch=16,col="red")
  lines((rep(ssegment[[n]][j],2))/dos,c(max(intensity[[n]]),0),col=2)
  lines((rep(esegment[[n]][j],2))/dos,c(max(intensity[[n]]),0),col=2)
 }

 dev.off()
 
 
if(s_flag==-1 & e_flag==-1)
 write.table(rbind((spoint[i]/dos),(spike/dos),(epoint[i]/dos)),par_name[i],sep="\t",col.name=F,row.name=F,append=F)
if(s_flag==-1 & e_flag==1)
 write.table(rbind((spoint[i]/dos),(spike/dos)),par_name[i],sep="\t",col.name=F,row.name=F,append=F)
if(s_flag==1 & e_flag==-1)
 write.table(rbind((spike/dos),(epoint[i]/dos)),par_name[i],sep="\t",col.name=F,row.name=F,append=F)
if(s_flag==1 & e_flag==1)
 write.table(as.matrix(spike/dos),par_name[i],sep="\t",col.name=F,row.name=F,append=F)
}
