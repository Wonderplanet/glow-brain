using UnityHTTPLibrary.Authenticate.Session;

namespace WPFramework.Domain.Models
{
    public record AuthorizationModel(ISessionStore SessionStore)
    {
        public ISessionStore SessionStore { get; } = SessionStore;
    }
}
