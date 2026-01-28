using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageNotStartException : WrappedServerErrorException
    {
        public StageNotStartException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
