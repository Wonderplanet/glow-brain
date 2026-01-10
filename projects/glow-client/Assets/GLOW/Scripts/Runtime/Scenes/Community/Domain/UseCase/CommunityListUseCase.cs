using GLOW.Core.Constants;
using GLOW.Core.Domain.ValueObjects.Community;
using GLOW.Scenes.Community.Domain.Model;

namespace GLOW.Scenes.Community.Domain.UseCase
{
    public class CommunityListUseCase
    {
        public CommunityListUseCaseModel GetCommunityList()
        {
            // ジャンブルラッシュ 公式サイト
            var jumbleRushOfficialSiteModel = new CommunityListModel(
                new CommunityUrl(Credentials.MediaJumbleRushOfficialSiteURL),
                new CommunityUrlSchema(""));
            
            // ジャンブルラッシュ 公式X
            var jumbleRushOfficialXModel = new CommunityListModel(
                new CommunityUrl(Credentials.MediaJumbleRushOfficialXURL),
                new CommunityUrlSchema(Credentials.MediaJumbleRushOfficialXURLSchema));
            
            // ジャンプ+ 公式サイト
            var jumpPlusOfficialSiteModel = new CommunityListModel(
                new CommunityUrl(Credentials.MediaJumpPlusOfficialSiteURL),
                new CommunityUrlSchema(""));
            
            // ジャンプ+連携サイト
            var jumpPlusLinkModel = new CommunityListModel(
                new CommunityUrl(Credentials.MediaJumpPlusLinkURL),
                new CommunityUrlSchema(""));
            
            // ジャンプ+ 公式X
            var jumpPlusOfficialXModel = new CommunityListModel(
                new CommunityUrl(Credentials.MediaJumpPlusOfficialXURL),
                new CommunityUrlSchema(Credentials.MediaJumpPlusOfficialXURLSchema));


            return new CommunityListUseCaseModel(
                jumbleRushOfficialSiteModel,
                jumbleRushOfficialXModel,
                jumpPlusOfficialSiteModel,
                jumpPlusLinkModel,
                jumpPlusOfficialXModel);
        }
    }
}