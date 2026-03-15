using System;

namespace WPFramework.Exceptions
{
    public sealed class AssetBundleDownloadPermissionRefusedException : ApplicationException
    {
        public AssetBundleDownloadPermissionRefusedException()
        {
        }

        public AssetBundleDownloadPermissionRefusedException(string message) : base(message)
        {
        }
    }
}
