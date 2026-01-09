using System.Collections.Generic;
using UnityEngine;
using UnityEngine.Serialization;

namespace GLOW.Scenes.HomeHelpDialog.Domain.ScriptableObjects
{
    [CreateAssetMenu(fileName = "home_help_info_list", menuName = "GLOW/ScriptableObject/HomeHelpInfoList")]
    public class HomeHelpInfoList : ScriptableObject
    {
        [Header("ヘルプ画面のデータ設定\n\n" +
                "MinorInfoのArticleに画像を設定する場合は、\n"+
                "行の頭に「!」を入れた後、続けてファイル名を指定してください。\n" +
                "設定する画像はhome_imageに入れてください。\n" +
                "１つの行にテキストと画像を一緒に表示することは出来ません。")]
        public List<HomeHelpMainContentInfo> MainContentInfoList;
    }
}
