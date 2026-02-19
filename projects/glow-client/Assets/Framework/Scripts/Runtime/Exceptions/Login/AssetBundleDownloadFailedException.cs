using System;

namespace WPFramework.Exceptions
{
    public class AssetBundleDownloadFailedException : Exception
    {
        public AssetBundleDownloadFailedException()
        {
        }

        public AssetBundleDownloadFailedException(string message) : base(message)
        {
        }
    }
}
