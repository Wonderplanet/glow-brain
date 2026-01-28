using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public abstract class AbstractAttackView : MonoBehaviour
    {
        public AttackId Id { get; protected set; }

        public abstract bool Initialize(IAttackModel attackModel, IViewCoordinateConverter viewCoordinateConverter);
        public abstract void UpdateAttackView(IAttackModel attackModel);
        public abstract void OnEndAttack(IAttackModel attackModel);
        public abstract bool IsEnd();
        public abstract void Pause(MultipleSwitchHandler handler);
    }
}
