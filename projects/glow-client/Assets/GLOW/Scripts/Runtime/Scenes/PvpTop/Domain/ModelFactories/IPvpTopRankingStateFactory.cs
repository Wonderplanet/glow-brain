using GLOW.Core.Domain.Models.Pvp;
using GLOW.Scenes.PvpTop.Domain.Model;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public interface IPvpTopRankingStateFactory
    {
        PvpTopRankingState Create(
            MstPvpModel mstPvpModel,
            SysPvpSeasonModel sysPvpSeasonModel,
            ViewableRankingFromCalculatingFlag isViewableRankingFromCalculating);
    }
}
