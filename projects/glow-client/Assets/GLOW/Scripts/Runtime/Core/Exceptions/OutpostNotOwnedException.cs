using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class OutpostNotOwnedException : DataInconsistencyServerErrorException
    {
        public OutpostNotOwnedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
