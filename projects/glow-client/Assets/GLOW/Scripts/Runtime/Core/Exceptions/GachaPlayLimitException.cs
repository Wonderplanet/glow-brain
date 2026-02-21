using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class GachaPlayLimitException : DataInconsistencyServerErrorException
    {
        public GachaPlayLimitException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
