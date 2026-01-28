using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class ArtworkNotOwnedException : DataInconsistencyServerErrorException
    {
        public ArtworkNotOwnedException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}
