using UnityHTTPLibrary;

namespace GLOW.Core.Exceptions
{
    public class PartyDuplicateArtworkIdException : WrappedServerErrorException
    {
        public PartyDuplicateArtworkIdException(ServerErrorException serverErrorException) : base(serverErrorException)
        {
        }
    }
}

