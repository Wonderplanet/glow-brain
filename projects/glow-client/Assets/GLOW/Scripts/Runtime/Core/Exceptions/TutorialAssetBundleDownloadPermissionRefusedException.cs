using System;

namespace GLOW.Core.Exceptions
{
    public sealed class TutorialAssetBundleDownloadPermissionRefusedException : ApplicationException
    {
        public TutorialAssetBundleDownloadPermissionRefusedException()
        {
        }

        public TutorialAssetBundleDownloadPermissionRefusedException(string message) : base(message)
        {
        }
    }
}