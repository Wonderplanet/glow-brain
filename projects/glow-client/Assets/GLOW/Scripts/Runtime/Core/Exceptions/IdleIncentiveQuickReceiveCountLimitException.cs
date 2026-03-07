using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class IdleIncentiveQuickReceiveCountLimitException : DataInconsistencyServerErrorException
    {
        public IdleIncentiveQuickReceiveCountLimitException(ServerErrorException serverErrorException) 
            : base(serverErrorException)
        {
        }
    }
}
