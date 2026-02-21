using GLOW.Core.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Modules.ArgumentURLEncrypter.Domain;
using Zenject;

namespace GLOW.Scenes.Inquiry.Domain.UseCases
{
    public class GetInquiryModelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public InquiryUseCaseModel GetInquiryModel()
        {
            var userProfileModel = GameRepository.GetGameFetchOther().UserProfileModel;
            
            // 20桁の任意の文字列
            var salt = RandomStringGenerator.Generate(20);
            
#if UNITY_ANDROID
            var userCommonKey = Credentials.ApplicationUserIdBNELinkKeyAndroid;
            var link = Credentials.BNEURLLinkKeyAndroid;
            var encryptedUserId = ArgumentURLEncrypter.Encrypt(userProfileModel.MyId.Value, userCommonKey, salt);
            var inquiryURL = Credentials.InquiryGooglePlayURL;
#elif UNITY_IOS
            var userCommonKey = Credentials.ApplicationUserIdBNELinkKeyIOS;
            var link = Credentials.BNEURLLinkKeyIos;
            var encryptedUserId = ArgumentURLEncrypter.Encrypt(userProfileModel.MyId.Value, userCommonKey, salt);
            var inquiryURL = Credentials.InquiryAppStoreURL;
            
#endif // UNITY_ANDROID or UNITY_IOS
            
            inquiryURL += "?link=" + link;  // 連携キー
            inquiryURL += "&user_id=" + encryptedUserId["uid"]; // 暗号化したユーザーid
            inquiryURL += "&dt=" + encryptedUserId["dt"];       // 日付
            inquiryURL += "&tm=" + encryptedUserId["tm"];       // 時間
            inquiryURL += "&rid=" + encryptedUserId["rid"];     // ランダムな4桁
            
            return new InquiryUseCaseModel(userProfileModel.MyId, inquiryURL);
        }
    }
}
