using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class IdleIncentiveCannotReceiveRewardException : WrappedServerErrorException
    {
        public IdleIncentiveCannotReceiveRewardException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
