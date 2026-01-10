using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.QuestContentTop.Domain.Factory
{
    public interface IPvpChallengeStatusFactory
    {
        PvpChallengeStatus Create(
            PvpItemChallengeCost pvpItemChallengeCost,
            UserPvpStatusModel userPvpStatusModel);
    }
}
