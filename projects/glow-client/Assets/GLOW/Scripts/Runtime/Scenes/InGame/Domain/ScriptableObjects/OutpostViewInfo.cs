using UnityEngine;

namespace GLOW.Scenes.InGame.Domain.ScriptableObjects
{
    [CreateAssetMenu(fileName = "OutpostViewInfo", menuName = "GLOW/ScriptableObject/OutpostViewInfo")]
    public class OutpostViewInfo : ScriptableObject
    {
        [Header("設定必須。OutpostSpriteViewを継承したクラスのAddComponent必須")]
        public GameObject OutpostPrefab;
        [Header("プレイヤーゲート用の＋マークオブジェクト")]
        public GameObject PlayerMarkPrefab;
        [Header("敵ゲート用の＋マークオブジェクト")]
        public GameObject EnemyMarkPrefab;
        [Header("味方ユニット召喚時のエフェクト（＋マークの発光）")]
        public GameObject PlayerSummonEffect;
        [Header("敵ユニット召喚時のエフェクト（ーマークの発光）")]
        public GameObject EnemySummonEffect;
        [Header("被ダメージ時のエフェクト")]
        public GameObject DamageEffect;
    }
}
