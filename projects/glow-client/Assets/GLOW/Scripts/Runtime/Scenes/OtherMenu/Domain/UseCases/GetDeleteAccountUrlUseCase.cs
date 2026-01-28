using GLOW.Core.Constants;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Localization;
using GLOW.Core.Domain.Repositories;
using GLOW.Modules.ArgumentURLEncrypter.Domain;
using Zenject;

namespace GLOW.Scenes.OtherMenu.Domain
{
    public class GetDeleteAccountUrlUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public string GetDeleteAccountUrl()
        {
            var userProfileModel = GameRepository.GetGameFetchOther().UserProfileModel;

            // 20桁の任意の文字列
            var salt = RandomStringGenerator.Generate(20);

            var userCommonKey = Credentials.ApplicationUserIdBNELinkKeyDelete;
            var link = Credentials.BNEURLLinkKeyDelete;
            var encryptedUserId = ArgumentURLEncrypter.Encrypt(userProfileModel.MyId.Value, userCommonKey, salt);
            var inquiryURL = Credentials.InquiryAccountDelete;

            inquiryURL += "?direct=jumblerush_PP_net";
            inquiryURL += "&link=" + link;  // 連携キー
            inquiryURL += "&user_id=" + encryptedUserId["uid"]; // 暗号化したユーザーid
            inquiryURL += "&dt=" + encryptedUserId["dt"];       // 日付
            inquiryURL += "&tm=" + encryptedUserId["tm"];       // 時間
            inquiryURL += "&rid=" + encryptedUserId["rid"];     // ランダムな4桁
            inquiryURL += "&lang=" + LanguageConverter.ToUrlParameterCode(Language.ja);

            return inquiryURL;
        }
    }
}
