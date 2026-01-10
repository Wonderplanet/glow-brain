using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Extensions;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using Zenject;

namespace GLOW.Scenes.ShopTab.Domain.UseCase
{
    public class GetGachaNoticeUseCase
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }

        public bool GetGachaNotice()
        {
            // 広告ガチャが引けるかどうか
            var now = TimeProvider.Now;
            var gachaModels = OprGachaRepository.GetOprGachaModelsByDataTime(now);

            foreach (var model in gachaModels)
            {
                var gameFetchOther = GameRepository.GetGameFetchOther();
                
                var userGachaModel = gameFetchOther.UserGachaModels.FirstOrDefault(
                    userGachaModel => userGachaModel.OprGachaId == model.GachaId,
                    UserGachaModel.Empty);
                
                if (userGachaModel.IsEmpty())
                {
                    userGachaModel = UserGachaModel.CreateById(model.GachaId);
                }

                // 広告で引けるか
                if (GachaEvaluator.IsFreePlay(model, userGachaModel))
                {
                    return true;
                }
            }

            return false;
        }
    }
}
