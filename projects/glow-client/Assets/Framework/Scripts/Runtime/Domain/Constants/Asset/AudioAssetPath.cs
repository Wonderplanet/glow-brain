namespace WPFramework.Domain.Constants
{
    public static class AudioAssetPath
    {
        public static string GetSePath(string key)
        {
            return $"audio_se_{key}";
        }

        public static string GetBGMPath(string key)
        {
            return $"audio_bgm_{key}";
        }
    }
}
