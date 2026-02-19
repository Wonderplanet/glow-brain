using UnityEngine;

// ReSharper disable InconsistentNaming

namespace GLOW.Scenes.InGame.Data.Data
{
    [CreateAssetMenu(fileName = "InGameSettingData", menuName = "GLOW/ScriptableObject/InGameSettingData")]
    public class InGameSettingData : ScriptableObject
    {
        [Header("スリップダメージ情報")]
        public int SlipDamageInterval;
        [Header("毒ダメージ情報")]
        public int PoisonDamageInterval;
        [Header("火傷ダメージ情報")]
        public int BurnDamageInterval;
        [Header("HP継続回復情報")]
        public int RegenerationInterval;
        [Header("不使用")]
        public int SpecialAttackCoolTime;
    }
}
