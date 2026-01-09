#if GLOW_INGAME_DEBUG
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Debugs.InGame.Presentation.DebugIngameLogView
{
    public class DebugIngameLogViewerItem : MonoBehaviour
    {
        [SerializeField] Text _logAreaText;

        public void SetUnitViewModel(CharacterUnitModel model)
        {
            string effects = "";
            if(1<=model.StateEffects.Count) effects = model.StateEffects.Select(m => m.Type.ToString()).Aggregate((result,current ) => result + ", " + current);
            //ここにユニットのテキスト情報を表示する
            _logAreaText.text = $"= {model.AssetKey.Value} =\n" +
                                $"HP: {model.Hp.Value} / {model.MaxHp.Value}\n" +
                                $"ATK: {model.AttackPower.Value}\n" +
                                $"Speed: {model.UnitMoveSpeed.Value}\n" +
                                $"Effects: {effects}\n" +
                                $"koma: {model.LocatedKoma.Id.Value} / prev: {model.PrevLocatedKoma.Id.Value}";
        }

        public void SetDeckViewModel(DeckUnitModel model)
        {
            if(model.IsEmptyUnit()) { return; }

            if (model.RoleType == CharacterUnitRoleType.Special)
            {
                _logAreaText.text = $"= {model.CharacterId.Value} =\n" +
                                    "スペシャルキャラ：CTなし";
            }
            else
            {
                _logAreaText.text = $"= {model.CharacterId.Value} =\n" +
                                    $"必CT_初:{model.SpecialAttackInitialCoolTime.Value}({model.SpecialAttackInitialCoolTime.ToSeconds():F2}s)\n" +
                                    $"必CT_2:{model.SpecialAttackCoolTime.Value}({model.SpecialAttackCoolTime.ToSeconds():F2}s)\n" +
                                    $"<color=red>必CT_現:{model.RemainingSpecialAttackCoolTime.Value}({model.RemainingSpecialAttackCoolTime.ToSeconds():F2}s)</color>\n" +
                                    $"召CT: {model.SummonCoolTime.Value}({model.SummonCoolTime.ToSeconds():F2}s)\n" +
                                    $"<color=red>召CT_現:{model.RemainingSummonCoolTime.Value}({model.RemainingSummonCoolTime.ToSeconds():F2}s)</color>";
            }
        }

        public void RefreshText()
        {
            _logAreaText.text = "";
        }

    }
}
#endif
