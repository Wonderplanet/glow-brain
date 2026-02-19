using GLOW.Core.Constants;

namespace GLOW.Core.Modules.Advertising.AppIdResolver
{
    public class DevelopGlowRewardAppIdResolver : IGlowRewardAppIdResolver
    {
        public GlowRewardAppId Resolve()
        {
            //ios, androidのappId切り替えはCredentialsの方で行っている
            return new GlowRewardAppId(Credentials.IAAAppId);
        }
    }
}
