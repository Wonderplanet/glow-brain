using System;

namespace WPFramework.Exceptions
{
    public sealed class MstDataDownloadPermissionRefusedException : ApplicationException
    {
        public MstDataDownloadPermissionRefusedException()
        {
        }

        public MstDataDownloadPermissionRefusedException(string message) : base(message)
        {
        }
    }
}
