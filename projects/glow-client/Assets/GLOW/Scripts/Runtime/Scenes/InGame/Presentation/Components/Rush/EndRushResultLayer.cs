using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components.Rush
{
    public class EndRushResultLayer : UIObject
    {
        [Header("総攻撃ダメージテキスト")]
        [SerializeField] UIText _rushResultScoreText;
        
        [Header("グレード表示(評価に該当するもののみ表示する)")]
        [SerializeField] UIObject _goodGradeComponent;
        [SerializeField] UIObject _greatGradeComponent;
        [SerializeField] UIObject _excellentGradeComponent;
        [SerializeField] UIObject _fantasticGradeComponent;
        
        [Header("パーティクル(評価が高いほど表示するものが増える)")]
        [SerializeField] ParticleSystem _goodParticleSystem;
        [SerializeField] ParticleSystem _greatParticleSystem;
        [SerializeField] ParticleSystem _excellentParticleSystem;
        [SerializeField] ParticleSystem _fantasticParticleSystem;
        
        public void SetUpRushResult(
            RushChargeCount chargeCount,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType)
        {
            // 総攻撃ダメージ表示
            _rushResultScoreText.SetText(calculatedRushAttackPower.ToStringN0());
            
            // グレード表示(評価に該当するもののみ表示する)
            _goodGradeComponent.IsVisible = rushEvaluationType == RushEvaluationType.Good;
            _greatGradeComponent.IsVisible = rushEvaluationType == RushEvaluationType.Great;
            _excellentGradeComponent.IsVisible = rushEvaluationType == RushEvaluationType.Excellent;
            _fantasticGradeComponent.IsVisible = rushEvaluationType == RushEvaluationType.Fantastic;
            
            // パーティクル表示(評価が高いほど表示するものが増える)
            var goodParticleEmission = _goodParticleSystem.emission;
            var greatParticleEmission = _greatParticleSystem.emission;
            var excellentParticleEmission = _excellentParticleSystem.emission;
            var fantasticParticleEmission = _fantasticParticleSystem.emission;
            
            goodParticleEmission.enabled = rushEvaluationType >= RushEvaluationType.Good;
            greatParticleEmission.enabled = rushEvaluationType >= RushEvaluationType.Great;
            excellentParticleEmission.enabled = rushEvaluationType >= RushEvaluationType.Excellent;
            fantasticParticleEmission.enabled = rushEvaluationType >= RushEvaluationType.Fantastic;
        }

        public void SetUpOpponentRushResult(AttackPower calculatedRushAttackPower)
        {
            // 総攻撃ダメージ表示(敵の場合はここだけ)
            _rushResultScoreText.SetText(calculatedRushAttackPower.ToStringN0());
        }
    }
}