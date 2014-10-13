#spectrum=intensity[[1]]
#retentation_time=rtime[[1]]
baseline_correction=function(spectrum,retentation_time,peak_points,n, windows_size)
{
 lspectrum=length(spectrum)
 nseg=ceiling(lspectrum/peak_points)
 sigma=rep(0,nseg)
 lsegment=ceiling(length(spectrum)/nseg)
 for(i in 1:(nseg-1))
 {
  sigma[i]=sd(spectrum[((i-1)*lsegment+1):(i*lsegment)])
  i=i+1
  sigma[i]=sd(spectrum[((i-1)*lsegment+1):lspectrum])
 }
 min_sigma=n*median(sigma[(sigma>0)& !(is.na(sigma))])

 is.baseline=rep(0,length(spectrum))
 for(i in 1:((windows_size-1)/2))
 {
   is.baseline[i]=((max(spectrum[1:(i+((windows_size-1)/2))]))-(min(spectrum[1:(i+((windows_size-1)/2))])))
 }
 for(i in (1+((windows_size-1)/2)):(lspectrum-(windows_size-1)/2))
 {
   is.baseline[i]=(max(spectrum[(i-((windows_size-1)/2)):(i+((windows_size-1)/2))])-(min(spectrum[(i-((windows_size-1)/2)):(i+((windows_size-1)/2))])))
 }
 for(i in (1+(lspectrum-(windows_size-1)/2)):lspectrum)
 {
   is.baseline[i]=(max(spectrum[(i-((windows_size-1)/2)):(lspectrum)])-(min(spectrum[(i-((windows_size-1)/2)):(lspectrum)])))
 } 
 is.baseline[1]=0
 is.baseline[lspectrum]=0
 c.baseline=rep(0,length(spectrum))
 c.baseline[is.baseline<(n*min_sigma)]=1
 c.baseline=c.baseline*
            c(0,c.baseline[1:(length(spectrum)-1)])*
            c(c.baseline[2:(length(spectrum))],0)
 c.baseline[c(1,length(spectrum))]=1
 baseline=approx(c(retentation_time[c.baseline==1]),
#baseline=approx(c(retentation_time[is.baseline<(n*min_sigma)]),
                 c(spectrum[c.baseline==1]),retentation_time)$y
#                 c(spectrum[is.baseline<(n*min_sigma)]),retentation_time)$y
 smooth.baseline=rep(0,lspectrum)
 for(i in 1:10)
  smooth.baseline[i]=mean(baseline[1:(i+10)])
 for(i in 11:(lspectrum-10))
  smooth.baseline[i]=mean(baseline[(i-10):(i+10)])
 for(i in (lspectrum-9):lspectrum)
  smooth.baseline[i]=mean(baseline[(i-10):lspectrum]) 
 spectrum=spectrum-smooth.baseline 
 return(smooth.baseline)                 
}
