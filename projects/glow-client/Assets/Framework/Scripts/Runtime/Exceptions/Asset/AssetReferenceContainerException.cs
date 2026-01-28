using System;

namespace WPFramework.Exceptions
{
    public sealed class AssetReferenceContainerException : ApplicationException
    {
        public AssetReferenceContainerException(string message) : base(message)
        {
        }
    }
}
