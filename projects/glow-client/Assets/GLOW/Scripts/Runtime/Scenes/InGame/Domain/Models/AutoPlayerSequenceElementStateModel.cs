using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AutoPlayerSequenceElementStateModel(
        MstAutoPlayerSequenceElementModel ElementModel,
        ICommonConditionModel ActivationCondition,
        ICommonConditionModel DeactivationCondition,
        AutoPlayerSequenceElementActivatedFlag IsActivated,
        AutoPlayerSequenceElementDeactivatedFlag IsDeactivated,
        TickCount RemainingSummonInterval,
        TickCount RemainingActionDelay,
        AutoPlayerSequenceSummonCount RemainingSummonCount)
    {
        public static AutoPlayerSequenceElementStateModel Empty { get; } = new(
            MstAutoPlayerSequenceElementModel.Empty,
            EmptyCommonConditionModel.Instance,
            EmptyCommonConditionModel.Instance,
            AutoPlayerSequenceElementActivatedFlag.False,
            AutoPlayerSequenceElementDeactivatedFlag.False,
            TickCount.Empty,
            TickCount.Empty,
            AutoPlayerSequenceSummonCount.Empty);
    }
}
