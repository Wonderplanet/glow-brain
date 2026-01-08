using System;

namespace GLOW.Core.Exceptions
{
    public class MasterDataCreateModelFailedException : ApplicationException
    {
        public MasterDataCreateModelFailedException()
        {
        }

        public MasterDataCreateModelFailedException(string message) : base(message)
        {
        }
        public MasterDataCreateModelFailedException(string message, Exception inner) : base(message, inner) { }
    }
}
