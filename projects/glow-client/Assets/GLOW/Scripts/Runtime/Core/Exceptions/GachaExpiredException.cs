using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaExpiredException : DataInconsistencyServerErrorException
    {
        public GachaExpiredException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}