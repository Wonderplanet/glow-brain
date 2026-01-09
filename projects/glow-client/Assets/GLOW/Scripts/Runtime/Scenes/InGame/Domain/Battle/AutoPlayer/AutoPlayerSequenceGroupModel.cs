using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public record AutoPlayerSequenceGroupModel(
        AutoPlayerSequenceGroupId SequenceGroupId,
        IReadOnlyList<AutoPlayerSequenceElementStateModel> SequenceElementStateModels,
        TickCount ActiveStartTime
    )
    {
        public static AutoPlayerSequenceGroupModel Empty { get; } = new(
            AutoPlayerSequenceGroupId.Empty,
            new List<AutoPlayerSequenceElementStateModel>(),
            TickCount.Empty
        );
    };
}
