using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Extensions
{
    public static class InGameEnumDomainExtension
    {
        public static AutoPlayerSequenceActionType ToAutoPlayerSequenceActionType(this BattleSide battleSide)
        {
            return battleSide switch
            {
                BattleSide.Player => AutoPlayerSequenceActionType.SummonPlayerCharacter,
                BattleSide.Enemy => AutoPlayerSequenceActionType.SummonEnemy,
                _ => AutoPlayerSequenceActionType.None
            };
        }
        public static BattleSide ToBattleSide(this AutoPlayerSequenceActionType battleSide)
        {
            return battleSide switch
            {
                AutoPlayerSequenceActionType.SummonPlayerCharacter => BattleSide.Player,
                AutoPlayerSequenceActionType.SummonEnemy => BattleSide.Enemy,
                _ =>throw new Exception($"invalid battle side...{battleSide}")
            };
        }
    }
}
