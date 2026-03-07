using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public interface IPvpTopUserStateFactory
    {
        PvpTopUserState Create(PvpTopRankingState pvpRankingState, MstPvpModel mstPvpModel, UserPvpStatusModel userPvpStatusModel);
    }
}