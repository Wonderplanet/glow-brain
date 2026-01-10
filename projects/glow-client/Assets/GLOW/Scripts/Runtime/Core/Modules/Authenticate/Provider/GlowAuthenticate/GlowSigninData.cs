namespace GLOW.Core.Modules.Authenticate.Provider.GlowAuthenticate
{
    internal class GlowSigninData
    {
        public string AccessToken { get; }

        public GlowSigninData(string accessToken)
        {
            AccessToken = accessToken;
        }
    }
}
