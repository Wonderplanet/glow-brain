using System;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class DamageNumberDisplayComponent : UIObject
    {
        [SerializeField] AnimationPlayer _animationPlayer;
        [SerializeField] UIText _damageText;
        [SerializeField] UIText _weakDamageText;
        [SerializeField] Transform _textRootTransform;
        
        [Header("ダメージ表示時の文字色")]
        [Header("与ダメージ(通常)")]
        [SerializeField] Color _normalDamageColor;
        [Header("被ダメージ")]
        [SerializeField] Color _receivedDamageColor;
        [Header("与ダメージ(有利属性:赤)")]
        [SerializeField] Color _redWeakDamageColor;
        [Header("与ダメージ(有利属性:緑)")]
        [SerializeField] Color _greenWeakDamageColor;
        [Header("与ダメージ(有利属性:青)")]
        [SerializeField] Color _blueWeakDamageColor;
        [Header("与ダメージ(有利属性:黄)")]
        [SerializeField] Color _yellowWeakDamageColor;
        [Header("回復")]
        [SerializeField] Color _healColor;
        
        [Header("ダメージ表示時の文字のスケール値")]
        [Header("与ダメージ(通常)")]
        [SerializeField] float _normalDamageScale = 0.9f;
        [Header("被ダメージ")]
        [SerializeField] float _receivedDamageScale = 0.8f;
        [Header("与ダメージ(有利属性)")]
        [SerializeField] float _weakDamageScale = 1.1f;
        [Header("回復")]
        [SerializeField] float _healScale = 1.0f;
        
        public Action OnCompleted { get; set; }
        
        readonly MultipleSwitchController _pauseController = new ();

        protected override void Awake()
        {
            base.Awake();
            _pauseController.OnStateChanged = OnPause;
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            _pauseController.Dispose();
        }
        
        public void SetDamageText(
            Damage damage,
            Heal heal,
            BattleSide targetBattleSide,
            AttackDamageType attackDamageType,
            AdvantageUnitColorFlag isAdvantageColor,
            CharacterColor attackerColor)
        {
            // ダメージタイプと対象に応じて表示色と表示値を決定
            Color textColor;
            int displayValue;
            float scaleValue;
            if (attackDamageType == AttackDamageType.Heal)
            {
                // 回復は緑色(この場合だけheal.Valueを使用)
                textColor = _healColor;
                displayValue = heal.Value;
                scaleValue = _healScale;
            }
            else if (isAdvantageColor)
            {
                // 敵味方問わず有利色ダメージは決まった属性色
                textColor = GetWeakDamageColor(attackerColor);
                displayValue = damage.Value;
                scaleValue = _weakDamageScale;
            }
            else if (targetBattleSide == BattleSide.Player)
            {
                // プレイヤー側の通常被ダメージは赤色
                textColor = _receivedDamageColor;
                displayValue = damage.Value;
                scaleValue = _receivedDamageScale;
            }
            else
            {
                // 上記以外は全て通常与ダメージとして白色
                textColor = _normalDamageColor;
                displayValue = damage.Value;
                scaleValue = _normalDamageScale;
            }
            
            // 使用するテキストオブジェクトの設定(有利色ダメージ時は弱点表示用テキストを使用)
            _weakDamageText.IsVisible = isAdvantageColor;
            _damageText.IsVisible = !isAdvantageColor;
            var displayDamageText = isAdvantageColor ? _weakDamageText : _damageText;
            
            // TextMeshProのカラー指定用にRGBAの16進数文字列に変換
            var textColorCode = ColorUtility.ToHtmlStringRGBA(textColor);
            
            // ここでのダメージ数値表示ではカンマは不要
            displayDamageText.SetText(ZString.Format("<color=#{0}>{1}</color>", textColorCode, displayValue));
            displayDamageText.RectTransform.localScale = Vector3.one * scaleValue;
        }
        
        public MultipleSwitchHandler Pause(MultipleSwitchHandler handler)
        {
            return _pauseController.TurnOn(handler);
        }
        
        public void Play()
        {
            if (_animationPlayer == null) return;
            
            _animationPlayer.OnDone = OnAnimationCompleted;
            _animationPlayer.Play();
        }
        
        void OnPause(bool isPause)
        {
            if (_animationPlayer != null)
            {
                _animationPlayer.Pause(isPause);
            }
        }
        
        Color GetWeakDamageColor(CharacterColor attackerColor)
        {
            return attackerColor switch
            {
                CharacterColor.Red => _redWeakDamageColor,
                CharacterColor.Green => _greenWeakDamageColor,
                CharacterColor.Blue => _blueWeakDamageColor,
                CharacterColor.Yellow => _yellowWeakDamageColor,
                _ => new Color(34/255f, 34/255f, 34/255f, 1f) // ダークグレー(想定外の色の場合)
            };
        }
        
        void OnAnimationCompleted()
        {
            OnCompleted?.Invoke();
        }
    }
}