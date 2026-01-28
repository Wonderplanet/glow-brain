using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.Home.Domain.Models;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeFooterBalloonUseCase
    {
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        public HomeFooterBalloonUseCaseModel GetHomeFooterBalloonUseCaseModel()
        {
            return new HomeFooterBalloonUseCaseModel(
                CheckGachaOpen(),
                CheckOpeningAdventBattle(),
                CheckOpeningPvp()
                );
        }

        HomeFooterBalloonShownFlag CheckGachaOpen()
        {
            return new HomeFooterBalloonShownFlag(IsNewGacha() || IsDrawableStartDashGacha());
        }

        bool IsNewGacha()
        {
            var lastOpen = PreferenceRepository.GachaListViewLastOpenedDateTimeOffset;
            var openingGachas = OprGachaRepository.GetOprGachaModelsByDataTime(TimeProvider.Now);

            return openingGachas.Any(g => lastOpen <= g.StartAt);
        }

        bool IsDrawableStartDashGacha()
        {
            var oprId = MstConfigRepository.GetConfig(MstConfigKey.GachaStartDashOprId).Value.ToMasterDataId();
            if (oprId.IsEmpty()) return false;

            var startDashGachaOpr = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(oprId);
            if (startDashGachaOpr.IsEmpty()) return false;

            // まだ引ける回数であり、かつ期限が切れていない場合はtrueを返す
            var userGachaModel = GameRepository.GetGameFetchOther().UserGachaModels
                .FirstOrDefault(model => model.OprGachaId == oprId, UserGachaModel.CreateById(oprId));
            var hasReachedDrawLimitedCount = GachaEvaluator.HasReachedDrawLimitedCount(startDashGachaOpr, userGachaModel);

            var isExpired = GachaEvaluator.IsExpiredUnlockDuration(
                startDashGachaOpr.UnlockDurationHours,
                userGachaModel.GachaExpireAt,
                TimeProvider.Now);

            return !hasReachedDrawLimitedCount && !isExpired;
        }

        HomeFooterBalloonShownFlag CheckOpeningAdventBattle()
        {
            var openingAdventBattles = MstAdventBattleDataRepository.GetMstAdventBattleModels()
                .Where(m => CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    m.StartDateTime.Value,
                    m.EndDateTime.Value));

            return new HomeFooterBalloonShownFlag(openingAdventBattles.Any());
        }

        HomeFooterBalloonShownFlag CheckOpeningPvp()
        {
            var pvpSeason = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
            if (pvpSeason.IsEmpty())
            {
                return new HomeFooterBalloonShownFlag(false);
            }
            return new HomeFooterBalloonShownFlag(CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                pvpSeason.StartAt.Value,
                pvpSeason.EndAt.Value));
        }
    }
}
