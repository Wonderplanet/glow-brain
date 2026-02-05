using System;
using System.Net;
using System.Net.Sockets;
using System.Threading;
using Cysharp.Threading.Tasks;
using WonderPlanet.CultureSupporter.Extensions;
using WPFramework.Modules.Log;

namespace WPFramework.Modules.TimeCalibration
{
    public sealed class NtpCalibrator : ITimeCalibrator
    {
        readonly INtpContext _ntpContext;

        public NtpCalibrator(INtpContext ntpContext)
        {
            // NOTE: NTPの接続先設定のコンテキストを外部から受け取る
            _ntpContext = ntpContext;
        }

        async UniTask<long> ITimeCalibrator.Fetch(CancellationToken cancellationToken)
        {
            // NOTE: NTPパケットの作成 (リクエスト用)
            var ntpData = new byte[48];
            ntpData[0] = 0x1B;

            // NOTE: NTPサーバーにリクエストを送信
            var endpoint = new IPEndPoint(IPAddress.Any, 0);
            using var udpClient = new UdpClient(endpoint);

            ApplicationLog.Log(nameof(NtpCalibrator), $"NTPサーバーにリクエストを送信します。IP:{_ntpContext.Domain} Port:{_ntpContext.Port}");

            // NOTE: DNSを通さないほうが通じやすいがIP変わると困るのでDNS経由でアクセスする
            await udpClient.SendAsync(ntpData, ntpData.Length, _ntpContext.Domain, _ntpContext.Port);

            var responseData = await udpClient.ReceiveAsync();
            var buffer = responseData.Buffer;

            // NOTE: 1900年からの秒数を取得
            ulong secondsSince1900 = (uint)(buffer[40] << 24 | buffer[41] << 16 | buffer[42] << 8 | buffer[43]);
            var ntpTime =
                new DateTime(1900, 1, 1, 0, 0, 0, DateTimeKind.Utc)
                    .AddSeconds(secondsSince1900);

            return ntpTime.ToUnixTimeMilliseconds();
        }
    }
}
