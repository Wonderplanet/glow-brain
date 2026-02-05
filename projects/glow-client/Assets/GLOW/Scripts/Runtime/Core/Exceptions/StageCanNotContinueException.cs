using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageCanNotContinueException : WrappedServerErrorException
    {
        public StageCanNotContinueException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
