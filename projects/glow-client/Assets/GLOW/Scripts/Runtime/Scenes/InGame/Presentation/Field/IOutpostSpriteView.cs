using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public interface IOutpostSpriteView
    {
        void Initialize(
            GameObject spriteRoot,
            BattleSide battleSide,
            OutpostViewInfo viewInfo,
            PageComponent pageComponent);

        void PlayAnimation(
            OutpostSDAnimationType animationType,
            OutpostSDAnimationType nextAnimationType,
            bool ignoresPriority,
            Action onCompleted);

        void SetArtworkSprite(ArtworkAssetPath assetPath);
        void OnSummonUnit();
        void OnBreakDown(FieldViewCoordV2 fieldViewPos, Vector3 breakDownEffectOffset);
        void OnRecover();
        void OnHitAttacks(bool isDangerHp);
    }
}
