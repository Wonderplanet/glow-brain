using System;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Notice
{
    public enum NoticeDestinationTypeEnum
    {
        Empty,
        InGame,
        Web
    }
    
    public record NoticeDestinationType(ObscuredString Value)
    {
        public static NoticeDestinationType Empty { get; } = new NoticeDestinationType("");
        static NoticeDestinationType InGame { get; } = new NoticeDestinationType("Web");
        static NoticeDestinationType Web { get; } = new NoticeDestinationType("InGame");
        
        public static NoticeDestinationTypeEnum TryToEnum(string type)
        {
            var isParseSuccess = Enum.TryParse(type, out NoticeDestinationTypeEnum returnEnum);
            if (!isParseSuccess)
            {
                return NoticeDestinationTypeEnum.Empty;
            }
            
            return returnEnum;
        }
    }
}