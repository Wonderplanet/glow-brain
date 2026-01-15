using System;
using System.IO;

namespace WPFramework.Exceptions
{
    public sealed class DiskFullException : IOException
    {
        public DiskFullException()
        {
        }

        public DiskFullException(string message) : base(message)
        {
        }

        public DiskFullException(string message, Exception innerException) : base(message, innerException)
        {
        }
    }
}
