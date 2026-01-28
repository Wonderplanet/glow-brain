#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.UseCases;
using GLOW.Debugs.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.InGame.Presentation.DebugIngameLogView
{
    public class DebugIngameLogViewerView : UIView
    {
        [Header("TickCount/50fps")]
        [SerializeField] Text _tickCountText;
        [Header("ユニット")]
        [SerializeField] Text _currentUnitStatusText;
        [SerializeField] RectTransform _unitViewRect;
        [SerializeField] DebugIngameLogViewerItem _unitItem;
        [Header("ダメージ")]
        [SerializeField] GameObject _damageReportArea;
        [SerializeField] Text _logAreaText;
        [SerializeField] ScrollRect _rect;

        List<DebugIngameLogViewerItem> _unitItemList = new ();
        bool _inited;
        public GameObject DamageReportArea => _damageReportArea;

        const string CurrentUnitStatusFormat = "<size=16>現在：</size>\n    {0}";
        protected override void Awake()
        {
            base.Awake();
            InitUnitStatusArea();
            InitDamageReport();
            _inited = true;
        }
        void InitUnitStatusArea()
        {
            //ここでInstantiateしてpoolする(今は10個にしている)
            //たくさん出て100体とか出る可能性があるから、どうやって表示機構作るか考えたい
            for(var i =0; i < 10; i++)
            {
                var item = Instantiate(_unitItem, _unitViewRect);
                item.gameObject.SetActive(true);
                _unitItemList.Add(item);
            }
        }
        void InitDamageReport()
        {
            _damageReportArea.SetActive(false);
            _logAreaText.text = "";
        }

        public void UpdateDamageLog(DebugInGameLogDamageModel model)
        {
            var builder = ZString.CreateStringBuilder();

            AppendTargetName(ref builder, model.Side, model.TargetName);
            AppendHitType(ref builder, model.AttackHitType, model.AttackDamageType);
            AppendDamage(ref builder, model.Damage);
            AppendHeal(ref builder, model.Heal);
            AppendHp(ref builder, model.BeforeHp, model.AfterHp);

            _logAreaText.text += builder;
            _rect.verticalNormalizedPosition = 0;
        }

        void AppendTargetName(ref Utf16ValueStringBuilder builder, BattleSide battleSide, DamageDebugLogTargetName targetName)
        {
            builder.Append(battleSide switch
            {
                BattleSide.Player => "<color=lightblue>Player: ",
                _ => "<color=#ce91fc>Enemy: ",
            });

            builder.Append("[");
            builder.Append(targetName.Value);
            builder.Append("]</color>\n");
        }

        void AppendHitType(ref Utf16ValueStringBuilder builder, AttackHitType attackHitType, AttackDamageType attackDamageType)
        {
            builder.Append("        Type: ");
            builder.Append(attackHitType);
            builder.Append(",");
            builder.AppendLine(attackDamageType);
        }

        void AppendDamage(ref Utf16ValueStringBuilder builder, Damage damage)
        {
            builder.Append("        Damage: ");
            if (!damage.IsZero()) builder.Append("<color=red>");
            builder.Append(damage.Value);
            if (!damage.IsZero()) builder.Append("</color>");
            builder.AppendLine();
        }

        void AppendHeal(ref Utf16ValueStringBuilder builder, Heal heal)
        {
            builder.Append("        Heal: ");
            if (!heal.IsZero()) builder.Append("<color=#4dfa55>");
            builder.Append(heal.Value);
            if (!heal.IsZero()) builder.Append("</color>");
            builder.AppendLine();
        }

        void AppendHp(ref Utf16ValueStringBuilder builder, HP beforeHp, HP afterHp)
        {
            builder.Append("        HP: ");
            builder.Append(beforeHp.Value);
            builder.Append(" -> ");
            builder.AppendLine(afterHp.Value);
        }

        public void UpdateTickCount(int tickCount)
        {
            _tickCountText.text = "Tick: " + tickCount.ToString();
        }

        public void UpdateUnitStatus(IReadOnlyList<CharacterUnitModel> models, DebugUnitStatusType type)
        {
            if (!_inited) return;

            _currentUnitStatusText.text = ZString.Format(CurrentUnitStatusFormat, type.ToString());

            if (models == null || models.Count <= 0)
            {
                RefreshAll();
                return;
            }

            RefreshAll();
            for (int i = 0; i < models.Count; i++)
            {
                if (i >= _unitItemList.Count) break;
                _unitItemList[i].SetUnitViewModel(models[i]);
            }
        }

        public void UpdateDeckStatus(IReadOnlyList<DeckUnitModel> models, DebugUnitStatusType type)
        {
            if (!_inited) return;

            _currentUnitStatusText.text = ZString.Format(CurrentUnitStatusFormat, type.ToString());

            RefreshAll();
            if (models == null || models.Count <= 0)
            {
                return;
            }

            for (int i = 0; i < models.Count; i++)
            {
                if (i >= _unitItemList.Count) break;
                _unitItemList[i].SetDeckViewModel(models[i]);
            }
        }

        void RefreshAll()
        {
            foreach(var item in _unitItemList)
            {
                item.RefreshText();
            }
        }
    }
}
#endif
