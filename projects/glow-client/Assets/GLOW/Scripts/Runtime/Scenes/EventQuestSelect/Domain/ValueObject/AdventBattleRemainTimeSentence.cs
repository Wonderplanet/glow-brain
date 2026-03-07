using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Scenes.EventQuestSelect.Domain.ValueObject
{
    //開催までdd時間mm分
    //終了までdd時間mm分
    public record AdventBattleRemainTimeSentence(ObscuredString PrefixString, ObscuredString RemainingTimeString);
}
