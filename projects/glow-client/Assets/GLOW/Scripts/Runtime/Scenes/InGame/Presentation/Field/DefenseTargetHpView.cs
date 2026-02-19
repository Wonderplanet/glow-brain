using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using TMPro;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class DefenseTargetHpView : MonoBehaviour
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
        }

        public void SetHpText(HP hp)
        {
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
    }
}
