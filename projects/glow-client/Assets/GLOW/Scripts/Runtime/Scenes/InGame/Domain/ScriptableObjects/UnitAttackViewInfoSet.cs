using UnityEngine;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Domain.ScriptableObjects
{
    [CreateAssetMenu(fileName = "unit_attack_view_info_", menuName = "GLOW/ScriptableObject/AttackViewInfoSet")]
    public class UnitAttackViewInfoSet : ScriptableObject
    {
        public UnitAttackViewInfo NormalAttackViewInfo;
        public UnitAttackViewInfo SpecialAttackViewInfo;
    }
}