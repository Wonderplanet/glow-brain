using System;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class InGameGimmickObjectImage : MonoBehaviour
    {
        [SerializeField] Transform _effectRoot;
        [SerializeField, Tooltip("ギミックオブジェクトから敵に変換される時のエフェクトに掛けるスケール係数"), Range(0.3f, 3f)]
        float _transformToEnemyEffectScale = 1.0f;

        public Transform EffectRoot => _effectRoot != null ? _effectRoot : transform;
        public float TransformToEnemyEffectScale => _transformToEnemyEffectScale;
    }
}
