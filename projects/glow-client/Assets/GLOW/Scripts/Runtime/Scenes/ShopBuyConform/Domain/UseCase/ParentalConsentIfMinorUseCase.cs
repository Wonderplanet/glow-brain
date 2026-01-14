using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.ShopBuyConform.Domain.UseCase
{
    public class ParentalConsentIfMinorUseCase
    {
        const int AdultAge = 18;
        [Inject] IGameRepository GameRepository { get; }

        /// <summary>
        /// 未成年か確認し、親権者の同意が必要かどうかを返す
        /// </summary>
        public ShouldShowParentalConsentFlag GetShouldShowParentalConsentFlag()
        {
            var fetchOther = GameRepository.GetGameFetchOther();
            var age = fetchOther.UserStoreInfoModel.UserAge.Value;
            var flag = new ShouldShowParentalConsentFlag(age < AdultAge);

            return flag;
        }
    }
}
