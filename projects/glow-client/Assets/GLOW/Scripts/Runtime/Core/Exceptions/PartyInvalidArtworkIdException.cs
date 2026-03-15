using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyInvalidArtworkIdException : WrappedServerErrorException
    {
        public PartyInvalidArtworkIdException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}

