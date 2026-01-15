using System;

namespace WPFramework.Exceptions
{
    public sealed class MstDataDownloadSizeException : ApplicationException
    {
        public MstDataDownloadSizeException()
        {
        }

        public MstDataDownloadSizeException(string message) : base(message)
        {
        }
    }
}
