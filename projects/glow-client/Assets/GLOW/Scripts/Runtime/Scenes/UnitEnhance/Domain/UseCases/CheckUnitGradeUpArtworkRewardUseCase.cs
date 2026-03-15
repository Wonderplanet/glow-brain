using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class CheckUnitGradeUpArtworkRewardUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUnitEnhanceNotificationHelper UnitEnhanceNotificationHelper { get; }

        public CheckUnitGradeUpArtworkRewardFlag CheckUnitGradeUpArtworkReward(
            UserDataId userUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);

            return UnitEnhanceNotificationHelper.GetUnitGradeUpArtworkRewardNotification(userUnit);
        }
    }
}
