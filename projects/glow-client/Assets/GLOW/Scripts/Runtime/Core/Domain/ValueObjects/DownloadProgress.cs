using Cysharp.Text;

namespace GLOW.Core.Domain.ValueObjects
{
    public record DownloadProgress(float Value)
    {
        public static bool operator <=(DownloadProgress a, float b)
        {
            return a.Value <= b;
        }
        public static bool operator <=(float a, DownloadProgress b)
        {
            return a <= b.Value;
        }

        public static bool operator >=(DownloadProgress a, float b)
        {
            return a.Value >= b;
        }
        public static bool operator >=(float a, DownloadProgress b)
        {
            return a >= b.Value;
        }
        
        public static bool operator <(DownloadProgress a, float b)
        {
            return a.Value < b;
        }
        public static bool operator <(float a, DownloadProgress b)
        {
            return a < b.Value;
        }

        public static bool operator >(DownloadProgress a, float b)
        {
            return a.Value > b;
        }
        public static bool operator >(float a, DownloadProgress b)
        {
            return a > b.Value;
        }
        
        public string ToPercentageString()
        {
            return ZString.Format("{0}%",(int)(this.Value * 100));
        }

        public float ToRate()
        {
            return this.Value;
        }
    }
}