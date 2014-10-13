target_origine=sub("-processed.txt",".txt",targetname);
para_file=paste("P",target_origine,sep="");
time_point=read.table(para_file);
time=(1: ((max(time_point)-min(time_point))*60*freq))/(60*freq);
max_range=(max(time_point)-min(time_point))*60*freq;
max_range=floor(max_range);

target=read.table(paste(raw,targetname,sep=""),header=F,sep="\t");
target=target[1:max_range,];
write.table(target,paste(raw,targetname,sep=""),sep="\t",row.names=FALSE,col.names=FALSE);
for(j in 1:number)
{
	samplename=paste(name[j],end,sep="")
		sample=read.table(samplename,header=F,sep="")
		sample=sample[1:max_range,1];
	result_name=paste(raw,name[j],"-processed",end,sep="");
	result=read.table(result_name,header=F,sep="")
		result=result[1:max_range,1];
	write.table(result,result_name,row.names=FALSE,col.names=FALSE);


#               time=1:length(target[,1])
#               time=time/freq;
#               time=time/60;

	y.min=min(target)
		if(min(sample)<y.min){
			y.min=min(sample)
		}
	y.max=max(target)
		if(max(sample)>y.max){
			y.max=max(sample)
		}

	y.lim=c(y.min,y.max)
		correlation=cor(cbind(target,sample))
		png(paste(image,name[j],".png",sep=""), width=800, height=400)
		plot(time,target,xlab="Time(unit: minutes)",ylab="Intensity",ylim=y.lim,col=2,type="l")

		if(min(result)<y.min){
			y.min=min(result)
		}
	if(max(result)>y.max){
		y.max=max(result)
	}
	y.lim=c(y.min,y.max)
		plot.new
		matlines(time,sample,col=3)
		plot.new
		legend("topright",c(paste("target :",targetname),paste("sample :",samplename),paste("correlation: ",round(correlation[2,1],2))),col=2:3,lty=1,lwd=c(1,1,0))
		dev.off()

		correlation=cor(cbind(target,result))
		png(paste(image,name[j],"-processed.png",sep=""), width=800, height=400)

		plot(time,target,xlab="Time(unit: minutes)",ylab="Intensity",ylim=y.lim,col=2,type="l")
		plot.new
		matlines(time,result,col=4)
		plot.new
legend("topright",c(paste("target:",targetname),paste("alignment result",samplename),paste("correlation: ",round(correlation[2,1],2))),col=c(2,4),lty=1,lwd=c(1,1,0))
		dev.off()
}


