namespace GLOW.Core.Modules.Authenticate.Provider.GlowAuthenticate
{
    internal class GlowSignupData
    {
        public string IdToken { get; }

        public string Identifier { get; }

        public GlowSignupData(string idToken, string identifier)
        {
            IdToken = idToken;
            Identifier = identifier;
        }
    }
}
