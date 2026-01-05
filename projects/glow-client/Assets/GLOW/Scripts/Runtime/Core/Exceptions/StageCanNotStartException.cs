using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class StageCanNotStartException : DataInconsistencyServerErrorException
    {
        public StageCanNotStartException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
