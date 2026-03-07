using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ModelFactories;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.UseCase
{
    public class GetPvpTopRankingStateUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpTopRankingStateFactory PvpTopRankingStateFactory { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }

        public PvpTopRankingState GetState(ViewableRankingFromCalculatingFlag isViewableRankingFromCalculating)
        {
            var sysPvpSeasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;
            var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(sysPvpSeasonModel.Id);

            if (mstPvpModel.IsEmpty())
            {
                return PvpTopRankingState.Empty;
            }

            return PvpTopRankingStateFactory.Create(
                mstPvpModel,
                sysPvpSeasonModel,
                isViewableRankingFromCalculating);
        }
    }
}
