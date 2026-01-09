using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.BeginnerMission.Domain.UseCase;
using GLOW.Scenes.Home.Domain.Models;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeMainBannerUseCase
    {
        const int MaxBannerCount = 5;
        [Inject] IMstHomeBannerRepository MstHomeBannerRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IBeginnerMissionFinishedEvaluator BeginnerMissionFinishedEvaluator { get; }
        [Inject] IOprGachaRepository GachaRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public IReadOnlyList<HomeMainBannerUseCaseModel> GetHomeMainBannerModels()
        {
            var beginnerMissionAllCompleted = BeginnerMissionFinishedEvaluator.CheckBeginnerMissionAllCompleted();

            return MstHomeBannerRepository.GetMstHomeBanners()
                .Where(m => !beginnerMissionAllCompleted || m.DestinationType != HomeBannerDestinationType.BeginnerMission)
                .Where(IsBannerActive)
                .OrderByDescending(m => m.SortOrder)
                .Select(m => new HomeMainBannerUseCaseModel(
                    m.BannerAssetKey,
                    m.DestinationType,
                    m.DestinationPath
                ))
                .Take(MaxBannerCount)
                .ToList();
        }

        // 開催期間チェック
        bool IsBannerActive(MstHomeBannerModel mstHomeBanner)
        {
            if (mstHomeBanner == null)
            {
                return false;
            }

            // バナー自体の開催期間チェック
            if (!CalculateTimeCalculator.IsValidTime(
                TimeProvider.Now,
                mstHomeBanner.StartAt,
                mstHomeBanner.EndAt))
            {
                return false;
            }

            // ガチャだった場合は、ガチャの開催期間もチェック
            if (mstHomeBanner.DestinationType == HomeBannerDestinationType.Gacha)
            {
                var gachaId = new MasterDataId(mstHomeBanner.DestinationPath.Value);
                var gacha = GachaRepository.GetOprGachaModelFirstOrDefaultById(gachaId);
                if (gacha.IsEmpty())
                {
                    return false;
                }

                if (!CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    gacha.StartAt,
                    gacha.EndAt))
                {
                    return false;
                }
            }

            if (mstHomeBanner.DestinationType == HomeBannerDestinationType.Pvp)
            {
                var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
                if (sysPvpSeasonModel.IsEmpty())
                {
                    return false;
                }

                if (!CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    sysPvpSeasonModel.StartAt.Value,
                    sysPvpSeasonModel.EndAt.Value))
                {
                    return false;
                }
            }
            return true;
        }
    }
}
