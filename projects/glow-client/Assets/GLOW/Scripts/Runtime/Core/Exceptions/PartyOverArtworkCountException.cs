using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyOverArtworkCountException : WrappedServerErrorException
    {
        public PartyOverArtworkCountException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}

