using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Presentation.Constants;
using TMPro;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class OutpostHpView : MonoBehaviour
    {
        const float HpTextAlignmentThresholdDigit = 4;

        [SerializeField] TextMeshPro _hpText;
        [SerializeField] TextMeshPro _hpOutlineText;
        [SerializeField] TMP_FontAsset _dangerHpFont;
        [SerializeField] Material _dangerHpTextMaterial;
        [SerializeField] TMP_FontAsset _dangerHpOutlineFont;
        [SerializeField] Material _dangerHpOutlineTextMaterial;

        BattleSide _battleSide;
        TMP_FontAsset _defaultHpFont;
        TMP_FontAsset _defaultHpOutlineFont;
        Material _defaultHpTextMaterial;
        Material _defaultHpOutlineTextMaterial;
        bool _isDanger;
        float _defaultLocalZPos;

        void Awake()
        {
            _defaultHpFont = _hpText.font;
            _defaultHpOutlineFont = _hpOutlineText.font;
            _defaultHpTextMaterial = _hpText.fontMaterial;
            _defaultHpOutlineTextMaterial = _hpOutlineText.fontMaterial;
        }

        public void Initialize(BattleSide battleSide)
        {
            _battleSide = battleSide;
            _defaultLocalZPos = transform.localPosition.z;
        }

        public void SetInvisibleHPText()
        {
            const string invisibleHP = "∞";
            _hpText.text = invisibleHP;
            _hpOutlineText.text = invisibleHP;
        }

        public void SetHpText(HP hp)
        {
            HorizontalAlignmentOptions horizontalAlignment = HorizontalAlignmentOptions.Center;

            if (hp.Digit > HpTextAlignmentThresholdDigit)
            {
                horizontalAlignment = _battleSide == BattleSide.Player
                    ? HorizontalAlignmentOptions.Right
                    : HorizontalAlignmentOptions.Left;
            }

            _hpText.horizontalAlignment = horizontalAlignment;
            _hpOutlineText.horizontalAlignment = horizontalAlignment;

            _hpText.text = hp.ToString();
            _hpOutlineText.text = hp.ToString();
        }

        public void SwitchDanger(bool isDanger)
        {
            if (isDanger == _isDanger) return;

            _isDanger = isDanger;

            _hpText.font = _isDanger ? _dangerHpFont : _defaultHpFont;
            _hpText.fontMaterial = _isDanger ? _dangerHpTextMaterial : _defaultHpTextMaterial;

            _hpOutlineText.font = _isDanger ? _dangerHpOutlineFont : _defaultHpOutlineFont;
            _hpOutlineText.fontMaterial = _isDanger ? _dangerHpOutlineTextMaterial : _defaultHpOutlineTextMaterial;
        }

        /// <summary> バトル開始時の強調表示 </summary>
        public void SetPlayerOutpostHpHighlight(bool isHighlight)
        {
            if (isHighlight)
            {
                var pos = transform.position;
                pos.z = FieldZPositionDefinitions.Highlight;
                transform.position = pos;
            }
            else
            {
                var pos = transform.localPosition;
                pos.z = _defaultLocalZPos;
                transform.localPosition = pos;
            }
        }
    }
}
