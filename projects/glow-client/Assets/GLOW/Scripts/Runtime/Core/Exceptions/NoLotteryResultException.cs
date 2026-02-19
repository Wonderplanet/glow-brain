using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class NoLotteryResultException : WrappedServerErrorException
    {
        public NoLotteryResultException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
