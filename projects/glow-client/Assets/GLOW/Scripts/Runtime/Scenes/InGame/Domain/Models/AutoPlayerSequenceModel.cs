using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record AutoPlayerSequenceModel(
        MstAutoPlayerSequenceModel MstAutoPlayerSequenceModel,
        IReadOnlyList<MstEnemyStageParameterModel> SummonEnemies,   // 召喚されるファントムのMstEnemyStageParameterModelリスト
        AutoPlayerSequenceSummonCount BossCount                     // ボスの合計召喚数
        )
    {
        public static AutoPlayerSequenceModel Empty { get; } = new(
            MstAutoPlayerSequenceModel.Empty,
            new List<MstEnemyStageParameterModel>(),
            AutoPlayerSequenceSummonCount.Empty
        );
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
