using System;

namespace GLOW.Core.Exceptions
{
    public class StoreProductNotFoundException : ApplicationException
    {
        public StoreProductNotFoundException()
        {
        }

        public StoreProductNotFoundException(string message) : base(message)
        {
        }
    }
}
