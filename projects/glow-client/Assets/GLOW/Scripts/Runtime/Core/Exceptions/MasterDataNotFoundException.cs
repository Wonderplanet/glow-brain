using System;

namespace GLOW.Core.Exceptions
{
    public class MasterDataNotFoundException : ApplicationException
    {
        public MasterDataNotFoundException()
        {
        }

        public MasterDataNotFoundException(string message) : base(message)
        {
        }
        public MasterDataNotFoundException(string message, Exception inner) : base(message, inner) { }
    }
}
