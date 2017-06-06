<?php
namespace library;
use library\Crawl;
use library\Out;
class CrawlCallback
{

    /**
     * 写入库
     *
     * @param array $row
     * @param obj $db
     * @param array $v
     */
    public static function detailWrite($row, $db, $v)
    {
        if (empty($v['content']))
            continue;
        $v['url'] = trim($v['url']);
        $data = array();
        $dataDetail = array();
        $dataId = md5($v['url']);
        foreach ($row['data'] as $configKey => $config) {
            $pattern = $config['rule'];
            preg_match($config['rule'], $v['content'], $match);
            if (! empty($match[1])) {
                // 是否要匹配图片
                if ($config['match_img'] == '2') {
                    $data[$config['field']] = trim(strip_tags(htmlspecialchars_decode($match[1]), $config['allowable_tags']));

                    preg_match_all('#<img.+src=\"?([^"].+\.?(jpg|gif|bmp|bnp|png|\"|\s)).+>#iU', $data[$config['field']], $imgMatch);
                    if(!empty($imgMatch[1])){
                     //print_r($imgMatch);
                        foreach($imgMatch[1] as $keyimgSrc => $imgSrc)
                        {
                            $imgSrc = trim(substr($imgSrc,0,300), '"');
                            if(empty($imgSrc)) continue;
                            $ext    = empty($imgMatch[2][$keyimgSrc])?strtolower($imgMatch[2][$keyimgSrc]):'jpg';
                            $dataImg[$keyimgSrc] = array(
                                'data_id' => $dataId,
                                'site_id' => $row['id'],
                                'ext'     => $ext,
                                'page_url'=> $v['url'],
                                'url'     => $row['img_dir'].self::imgUrl($imgSrc, $ext),
                                'source_url' => $imgSrc,
                            );

                            if(!empty($dataImg[$keyimgSrc]['url'])){
                                $search[$keyimgSrc]  = $dataImg[$keyimgSrc]['source_url'];
                                $replace[$keyimgSrc] = $row['img_url'].'/'.$dataImg[$keyimgSrc]['url'];
                            }
                        }

                        //图片替换成本地路径
                        if(!empty($search) && !empty($replace)){
                            $data[$config['field']] = str_replace($search, $replace, $data[$config['field']]);
                         }

                    }
                } else {
                    $data[$config['field']] = trim(strip_tags(htmlspecialchars_decode($match[1]), $config['allowable_tags']));
                }
            } else
            {
                Out::info("[match Notice] not match: url: {$config['rule']} ");
            }

            $dataDetail[] = array(
                'data_id' => $dataId,
                'site_id' => $row['id'],
                'name' => $config['field'],
                'value' => isset($data[$config['field']]) ? $data[$config['field']] : ''
            );
        }

        $set = array(
            'site_id'     => $row['id'],
            'url'         => $v['url'],
            'data_id'     => $dataId,
            'title'       => isset($data['title']) ? $data['title'] : '',
            'create_time' => date("Y-m-d H:i:s")
        );
        //$result  = false;
        $result = $db->insert('data', $set);
        if ($result) {

            if(!empty($dataImg))
            $db->insertAll('data_image', $dataImg);

            $db->insertAll('data_detail', $dataDetail);
        }
    }

    public static function listWrite($row, $db, $v)
    {
        if (empty($v['content']))
            continue;

        preg_match_all($row['item_rule_a'], $v['content'], $match);
        $filename = $row['project'] . '/detail.txt';
        if (empty($match[1])) {
            Out::info("[match Notice] not match: url: {$row['item_rule_a']}");
            return false;
        }
        $urlArr = [];
        foreach ($match[1] as $k2=>$v2) {
            $urlArr[$k2]['url'] = $v2;
        }

        $result = \library\Crawl::write($urlArr, $filename);
        if(empty($result)){
            echo("not data !");
            return false;
        }
        $data = [];
        foreach ($result as $v){
            $data[] = array(
                'url'      => $v['url'],
                'filesize' => $v['filesize'],
                'site_id'  => $row['id'],
                'type'     => 1
            );
        }

        return $db->insertAll('url', $data);

    }

    /**
     * 生成图片URL
     * @param int $id
     * @param int $ext 后娺
     */
    public static function imgUrl($id, $ext){
        $md5_id = md5($id);
        $path   = substr($md5_id, 0,2);
        $path1  = substr($md5_id, 2,2);
        return  '/' .$path . '/' . $path1 . '/' . $md5_id .'.'. $ext;
    }
}

