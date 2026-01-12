using System;

namespace GLOW.Core.Exceptions
{
    public class IAAPlayRewardException : ApplicationException
    {
        public IAAPlayRewardException()
        {
        }

        public IAAPlayRewardException(string message) : base(message)
        {
        }
    }
}