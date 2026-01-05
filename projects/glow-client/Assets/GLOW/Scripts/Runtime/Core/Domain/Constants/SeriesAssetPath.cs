using Cysharp.Text;

namespace GLOW.Core.Domain.Constants
{
    public static class SeriesAssetPath
    {
        public static string GetSeriesLogoPath(string seriesKey)
        {
            return ZString.Format("series_logo_{0}", seriesKey);
        }

        public static string GetSeriesBannerPath(string seriesKey)
        {
            return ZString.Format("series_banner_{0}", seriesKey);
        }
    }
}
